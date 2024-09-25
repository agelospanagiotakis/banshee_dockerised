<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class account_model extends Banshee\model {
		private $hashed = null;

		public function get_user($user_id) {
			$query = "select fullname, email, avatar ".
			         "from users where id=%d limit 1";

			if (($users = $this->db->execute($query, $user_id)) == false) {
				return false;
			}

			return $users[0];
		}

		public function get_organisation() {
			if (($result = $this->db->entry("organisations", $this->user->organisation_id)) == false) {
				return false;
			}

			return $result["name"];
		}

		public function last_account_logs() {
			if (($fp = fopen("../logfiles/actions.log", "r")) == false) {
				return false;
			}

			$result = array();

			while (($line = fgets($fp)) !== false) {
				$parts = explode("|", chop($line));
				if (count($parts) < 5) {
					continue;
				}

				list($ip, $timestamp, $user_id, $path, $message) = $parts;

				if ($user_id == "-") {
					continue;
				} else if ($user_id != $this->user->id) {
					continue;
				}

				array_push($result, array(
					"ip"        => $ip,
					"timestamp" => $timestamp,
					"message"   => $message));
				if (count($result) > 15) {
					array_shift($result);
				}
			}

			fclose($fp);

			return array_reverse($result);
		}

		public function account_okay($account) {
			$result = true;

			if (trim($account["fullname"]) == "") {
				$this->view->add_message("Fill in your name.");
				$result = false;
			}

			if (valid_email($account["email"]) == false) {
				$this->view->add_message("Invalid e-mail address.");
				$result = false;
			} else if (($check = $this->db->entry("users", $account["email"], "email")) != false) {
				if ($check["id"] != $this->user->id) {
					$this->view->add_message("E-mail address already exists.");
					$result = false;
				}
			}

			if (strlen($account["current"]) > PASSWORD_MAX_LENGTH) {
				$this->view->add_message("Current password is too long.");
				$result = false;
			} else if (password_verify($account["current"], $this->user->password) == false) {
				$this->view->add_message("Current password is incorrect.");
				$result = false;
			}

			if ($account["password"] != "") {
				if (is_secure_password($account["password"], $this->view) == false) {
					$result = false;
				} else if ($account["password"] != $account["repeat"]) {
					$this->view->add_message("New passwords do not match.");
					$result = false;
				} else if (password_verify($account["password"], $this->user->password)) {
					$this->view->add_message("New password must be different from current password.");
					$result = false;
				}

			}

			if (is_true(USE_AUTHENTICATOR)) {
				if ((strlen($account["authenticator_secret"]) > 0) && ($account["authenticator_secret"] != str_repeat("*", 16))) {
					if (valid_input($account["authenticator_secret"], Banshee\authenticator::BASE32_CHARS, 16) == false) {
						$this->view->add_message("Invalid authenticator secret.");
						$result = false;
					}
				}
			}

			return $result;
		}

		public function update_account($account) {
			$keys = array("fullname", "email", "avatar", "signature");

			if ($account["password"] != "") {
				array_push($keys, "password");
				array_push($keys, "status");

				if (is_true(ENCRYPT_DATA)) {
					array_push($keys,"crypto_key");

					$cookie = new \Banshee\secure_cookie($this->settings);
					$aes = new \Banshee\Protocol\AES256($account["password"]);
					$account["crypto_key"] = $aes->encrypt($cookie->crypto_key);
				}

				$account["password"] = password_hash($account["password"], PASSWORD_ALGORITHM);
				$account["status"] = USER_STATUS_ACTIVE;
			}

			if (is_true(USE_AUTHENTICATOR)) {
				if ($account["authenticator_secret"] != str_repeat("*", 16)) {
					array_push($keys, "authenticator_secret");
					if (trim($account["authenticator_secret"]) == "") {
						$account["authenticator_secret"] = null;
					}
				}
			}

			return $this->db->update("users", $this->user->id, $account, $keys) !== false;
		}

		public function delete_okay($account) {
			$result = true;

			if (strlen($account["current"]) > PASSWORD_MAX_LENGTH) {
				$this->view->add_message("Current password is too long.");
				$result = false;
			} else if (password_verify($account["current"], $this->user->password) == false) {
				$this->view->add_message("Current password is incorrect.");
				$result = false;
			}

			return $result;
		}

		public function delete_account() {
			if ($this->user->is_admin) {
				return false;
			}

			return $this->borrow("cms/user")->delete_user($this->user->id);
		}
	}
?>
