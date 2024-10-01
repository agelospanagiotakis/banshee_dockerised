<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class register_controller extends Banshee\splitform_controller {
		protected $button_submit = "Register";
		protected $ask_organisation = null;

		protected function prepare_code($data) {
			if (($_SESSION["register_email"] ?? null) == $data["email"]) {
				return;
			}

			$_SESSION["register_code"] = random_string(20);

			$email = new \Banshee\Protocol\email("Verification code for the ".$this->settings->head_title." website", $this->settings->webmaster_email);
			$email->set_message_fields(array(
				"CODE"    => $_SESSION["register_code"],
				"WEBSITE" => $this->settings->head_title));
			$email->message(file_get_contents("../extra/register.txt"));
			$email->send($data["email"]);

			$_SESSION["register_email"] = $data["email"];
			$this->model->set_value("code", "");
		}

		protected function prepare_account($data) {
			if (is_true(ENCRYPT_DATA)) {
				$this->view->run_javascript("add_delay_warning()");
			}

			if ($this->ask_organisation) {
				$this->ask_organisation = (($data["invitation"] ?? null) == "");
			}

			$this->view->add_tag("ask_organisation", show_boolean($this->ask_organisation));
		}

		protected function prepare_authenticator($data) {
			if (is_true(ENCRYPT_DATA)) {
				$this->view->run_javascript("add_delay_warning()");
			}
		}

		public function execute() {
			if ($this->user->logged_in) {
				$this->view->add_tag("result", "You already have an account.", array("url" => ""));
				return;
			}

			if ($this->page->ajax_request) {
				$authenticator = new \Banshee\authenticator;
				$this->view->add_tag("secret", $authenticator->create_secret());
				return;
			}

			$this->view->add_javascript("register.js");

			if ($_SERVER["REQUEST_METHOD"] == "GET") {
				$this->model->reset_form_progress();
			} else if (($_POST["splitform_current"] ?? null) == 2) {
				$_POST["username"] = strtolower($_POST["username"] ?? "");
			}

			$this->ask_organisation = (DEFAULT_ORGANISATION_ID == 0);

			parent::execute();
		}
	}
?>
