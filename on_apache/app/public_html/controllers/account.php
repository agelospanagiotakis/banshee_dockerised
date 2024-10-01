<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class account_controller extends Banshee\controller {
		private function view_user($user_id) {
			if (($user = $this->model->get_user($user_id)) == false) {
				$this->view->add_tag("result", "User not found.", array("url" => ""));
				return;
			}

			if ($user["avatar"] == "") {
				$user["avatar"] = EMPTY_AVATAR;
			}

			$this->view->title = $user["fullname"];

			$this->view->open_tag("view");
			$this->view->record($user, "user");
			$this->view->add_tag("previous", $this->page->previous);
			$this->view->close_tag();
		}

		private function show_account_form($account = null) {
			if ($account === null) {
				$account = array(
					"fullname"             => $this->user->fullname,
					"email"                => $this->user->email,
					"authenticator_secret" => str_repeat("*", strlen($this->user->authenticator_secret ?? "")),
					"avatar"               => $this->user->avatar,
					"signature"            => $this->user->signature);
			}

			if (($organisation = $this->model->get_organisation()) === false) {
				$this->view->add_tag("result", "Database error.");
				return false;
			}

			if (is_true(USE_AUTHENTICATOR)) {
				$this->view->add_javascript("webui/jquery-ui.js");
				$this->view->add_javascript("account.js");

				$this->view->add_css("webui/jquery-ui.css");
			}

			$this->view->open_tag("edit", array(
				"authenticator" => show_boolean(USE_AUTHENTICATOR),
				"logout"        => LOGOUT_MODULE));

			$this->view->add_tag("username", $this->user->username);
			$this->view->add_tag("organisation", $organisation);
			foreach (array_keys($account) as $key) {
				$this->view->add_tag($key, $account[$key]);
			}

			/* Action log
			 */
			if (($actionlog = $this->model->last_account_logs()) !== false) {
				$this->view->open_tag("actionlog");
				foreach ($actionlog as $log) {
					$this->view->record($log, "log");
				}
				$this->view->close_tag();
			}

			$this->view->close_tag();
		}

		public function execute() {
			if ($this->user->logged_in == false) {
				$this->view->add_tag("result", "You are not logged in!", array("url" => $this->settings->start_page));
				return;
			}

			$this->view->description = "Account";
			$this->view->keywords = "account";
			$this->view->title = "Account";

			if ($this->user->status == USER_STATUS_ACTIVE) {
				if ($this->page->parameter_numeric(0)) {
					$this->view_user($this->page->parameters[0]);
					return;
				}
			}

			if ($this->user->status == USER_STATUS_CHANGEPWD) {
				$this->view->add_message("Please, change your password.");
			}

			if (isset($_SESSION["account_next"]) == false) {
				if (($this->page->pathinfo[0] ?? null) == ACCOUNT_MODULE) {
					$_SESSION["account_next"] = $this->settings->start_page;
				} else {
					$_SESSION["account_next"] = substr($_SERVER["REQUEST_URI"], 1);
				}
			}

			if ($this->page->parameter_value(0, "authenticator") && $this->page->ajax_request) {
				$authenticator = new \Banshee\authenticator;
				$this->view->add_tag("secret", $authenticator->create_secret());
			} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
				/* Update account
				 */
				if ($_POST["submit_button"] == "Update account") {
					if ($this->model->account_okay($_POST) == false) {
						$this->show_account_form($_POST);
					} else if ($this->model->update_account($_POST) === false) {
						$this->view->add_tag("result", "Error while updating account.", array("url" => ACCOUNT_MODULE));
					} else {
						$this->view->add_tag("result", "Account has been updated.", array("url" => $_SESSION["account_next"]));
						$this->user->log_action("account updated");
						unset($_SESSION["account_next"]);
					}
				} else if ($_POST["submit_button"] == "Delete account") {
					if ($this->model->delete_okay($_POST) == false) {
						$this->show_account_form();
					} else if ($this->model->delete_account() == false) {
						$this->view->add_message("Something went wrong while deleting this account.");
						$this->show_account_form();
					} else {
						$this->view->add_tag("result", "Your account has been deleted. You are now logged out.", array("url" => ""));
						$this->user->logout();
					}
				}
			} else {
				$this->show_account_form();
			}
		}
	}
?>
