<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class register_model extends Banshee\splitform_model {
		const MINIMUM_USERNAME_LENGTH = 4;
		const MINIMUM_FULLNAME_LENGTH = 4;

		protected $forms = array(
			"invitation"    => array("invitation"),
			"email"         => array("email"),
			"code"          => array("code"),
			"account"       => array("fullname", "username", "password", "organisation"),
			"authenticator" => array("authenticator_secret"));

		public function __construct() {
			if ((DEFAULT_ORGANISATION_ID != 0) || is_true(ENCRYPT_DATA)) {
				unset($this->forms["invitation"]);
			}

			if (is_false(USE_AUTHENTICATOR)) {
				unset($this->forms["authenticator"]);
			}

			$arguments = func_get_args();
			call_user_func_array(array(parent::class, "__construct"), $arguments);
		}

		public function reset_form_progress() {
			unset($_SESSION["register_email"]);
			unset($_SESSION["register_code"]);

			parent::reset_form_progress();
		}

		public function validate_invitation($data) {
			if (empty($data["invitation"])) {
				return true;
			}

			if (strpos($data["invitation"], "-") === false) {
				$this->view->add_message("Invalid invitation code.");
				return false;
			}

			list($id, $code) = explode("-", $data["invitation"]);

			$query = "select * from organisations where id=%d and invitation_code=%s";
			if ($this->db->execute($query, $id, $code) == false) {
				$this->view->add_message("Invalid invitation code.");
				return false;
			}

			return true;
		}

		public function validate_email($data) {
			$result = true;

			if (valid_email($data["email"]) == false) {
				$this->view->add_message("Invalid e-mail address.");
				$result = false;
			}

			$query = "select * from users where email=%s";
			if ($this->db->execute($query, $data["email"]) != false) {
				$this->view->add_message("The e-mail address has already been used to register an account.");
				$result = false;
			}

			return $result;
		}

		public function validate_code($data) {
			if ($data["code"] != $_SESSION["register_code"]) {
				$this->view->add_message("Invalid verification code.");
				return false;
			}

			return true;
		}

		public function validate_account($data) {
			$result = true;

			$length_okay = (strlen($data["username"]) >= self::MINIMUM_USERNAME_LENGTH);
			$format_okay = valid_input($data["username"], VALIDATE_NONCAPITALS, VALIDATE_NONEMPTY);

			if (($length_okay == false) || ($format_okay == false)) {
				$this->view->add_message("Your username must consist of lowercase letters with a mimimum length of %d.", self::MINIMUM_USERNAME_LENGTH);
				$result = false;
			}

			$query = "select * from users where username=%s";
			if ($this->db->execute($query, $data["username"]) != false) {
				$this->view->add_message("The username is already taken.");
				$result = false;
			}

			if (is_secure_password($data["password"], $this->view) == false) {
				$result = false;
			}

			if (strlen($data["fullname"]) < self::MINIMUM_FULLNAME_LENGTH) {
				$this->view->add_message("The length of your name must be equal or greater than %d.", self::MINIMUM_FULLNAME_LENGTH);
				$result = false;
			}

			if ((DEFAULT_ORGANISATION_ID == 0) && empty($this->values["invitation"])) {
				if (trim($data["organisation"] == "")) {
					$this->view->add_message("Fill in the organisation name.");
					$result = false;
				} else {
					$query = "select * from organisations where name=%s";
					if ($this->db->execute($query, $data["organisation"]) != false) {
						$this->view->add_message("The organisation name is already taken.");
						$result = false;
					}
				}
			}

			return $result;
		}

		public function validate_authenticator($data) {
			$result = true;

			if ((strlen($data["authenticator_secret"]) > 0) && ($data["authenticator_secret"] != str_repeat("*", 16))) {
				if (valid_input($data["authenticator_secret"], Banshee\authenticator::BASE32_CHARS, 16) == false) {
					$this->view->add_message("Invalid authenticator secret.");
					$result = false;
				}
			}

			return $result;
		}

		public function process_form_data($data) {
			if ($this->db->query("begin") === false) {
				return false;
			}

			if (DEFAULT_ORGANISATION_ID != 0) {
				$organisation_id = DEFAULT_ORGANISATION_ID;
			} else if (empty($data["invitation"]) == false) {
				list($organisation_id) = explode("-", $data["invitation"]);
			} else {
				$organisation = array(
					"name" => $data["organisation"]);

				if (($organisation_id = $this->borrow("cms/organisation")->create_organisation($organisation, true)) == false) {
					return false;
				}
			}

			if (is_true(ENCRYPT_DATA)) {
				$crypto_key = random_string(CRYPTO_KEY_SIZE);

				$rsa = new \Banshee\Protocol\RSA((int)RSA_KEY_SIZE);
				$aes = new \Banshee\Protocol\AES256($crypto_key);
				$private_key = $aes->encrypt($rsa->private_key);
				$public_key = $rsa->public_key;

				$aes = new \Banshee\Protocol\AES256($data["password"]);
				$crypto_key = $aes->encrypt($crypto_key);
			} else {
				$private_key = null;
				$public_key = null;
				$crypto_key = null;
			}

			$user = array(
				"organisation_id" => $organisation_id,
				"username"        => $data["username"],
				"password"        => $data["password"],
			    "status"          => USER_STATUS_ACTIVE,
				"fullname"        => $data["fullname"],
				"email"           => $data["email"],
				"private_key"     => $private_key,
				"public_key"      => $public_key,
				"crypto_key"      => $crypto_key,
				"roles"           => array(USER_ROLE_ID));

			if (is_true(USE_AUTHENTICATOR)) {
				$user["authenticator_secret"] = $data["authenticator_secret"];
			}

			if ($this->borrow("cms/user")->create_user($user, true) == false) {
				return false;
			}

			$this->user->log_action("user %s registered", $data["username"]);

			unset($_SESSION["register_email"]);
			unset($_SESSION["register_code"]);

			$email = new \Banshee\Protocol\email("New account registered at ".$_SERVER["SERVER_NAME"], $this->settings->webmaster_email);
			$email->set_message_fields(array(
				"FULLNAME" => $data["fullname"],
				"EMAIL"    => $data["email"],
				"USERNAME" => $data["username"],
				"WEBSITE"  => $this->settings->head_title,
				"IP_ADDR"  => $_SERVER["REMOTE_ADDR"]));
			$email->message(file_get_contents("../extra/account_registered.txt"));
			$email->send($this->settings->webmaster_email);

			return $this->db->query("commit") !== false;
		}
	}
?>
