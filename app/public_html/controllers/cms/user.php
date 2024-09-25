<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_user_controller extends Banshee\controller {
		private function show_user_overview() {
			if (($user_count = $this->model->count_users()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			handle_table_sort("adminuser_order", array("id", "username", "fullname", "email", "status"), array("username", "id"));
			$pagination = new \Banshee\pagination($this->view, "admin_users", $this->settings->admin_page_size, $user_count);

			if ($user_count == 0) {
				$users = array();
			} else {
				$users = $this->model->get_users($_SESSION["adminuser_order"], $pagination->offset, $pagination->size);
			}

			$roles = $this->model->get_roles();
			if (($users === false) || ($roles === false)) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$status = array("Disabled", "Change password", "Active");

			$this->view->open_tag("overview", array("search" => $_SESSION["user_search"] ?? ""));

			$this->view->open_tag("users");
			foreach ($users as $user) {
				$user["status"] = $status[$user["status"]];

				$this->view->open_tag("user", array(
					"id"    => $user["id"],
					"admin" => show_boolean($user["is_admin"])));
				$this->view->add_tag("username", $user["username"]);
				$this->view->add_tag("fullname", $user["fullname"]);
				$this->view->add_tag("email", $user["email"]);
				$this->view->add_tag("status", $user["status"]);
				$this->view->close_tag();
			}
			$this->view->close_tag();

			if (empty($_SESSION["user_search"])) {
				$pagination->show_browse_links();
			}

			$this->view->close_tag();
		}

		private function show_user_form($user) {
			if (isset($user["roles"]) == false) {
				$user["roles"] = array();
			}

			if (($roles = $this->model->get_roles()) == false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			if ($this->model->allow_edit_organisation()) {
				if (($organisations = $this->model->get_organisations()) == false) {
					$this->view->add_tag("result", "Database error.");
					return;
				}
			} else if ($this->user->is_admin) {
				$user["organisation"] = $this->model->get_organisation($user["organisation_id"]);
			}

			$this->view->add_javascript("webui/jquery-ui.js");
			$this->view->add_javascript("cms/user.js");

			$this->view->add_css("webui/jquery-ui.css");

			$this->view->open_tag("edit", array("authenticator" => show_boolean(USE_AUTHENTICATOR)));

			$this->view->open_tag("status");
			$status = array(
				USER_STATUS_DISABLED =>  "Disabled",
				USER_STATUS_CHANGEPWD => "Change password",
				USER_STATUS_ACTIVE =>    "Active");
			foreach ($status as $id => $stat) {
				$this->view->add_tag("status", $stat, array("id" => $id));
			}
			$this->view->close_tag();

			$password = show_boolean($this->model->allow_edit_password($user));
			$this->view->record($user, "user", array("password" => $password));

			if ($this->model->allow_edit_organisation()) {
				$this->view->open_tag("organisations");
				foreach ($organisations as $organisation) {
					$this->view->add_tag("organisation", $organisation["name"], array("id" => $organisation["id"]));
				}
				$this->view->close_tag();
			}

			$this->view->open_tag("roles");
			foreach ($roles as $role) {
				/* Non-admins cannot assign the admin role
				 */
				if (($this->user->is_admin == false) && ($role["id"] == ADMIN_ROLE_ID)) {
					continue;
				}

				$checked = in_array($role["id"], $user["roles"]);
				$enabled = ($this->user->id != ($user["id"] ?? null)) || ($role["id"] != ADMIN_ROLE_ID); /* Don't disable yourself */

				$this->view->add_tag("role", $role["name"], array(
					"id"      => $role["id"],
					"checked" => show_boolean($checked),
					"enabled" => show_boolean($enabled)));
			}
			$this->view->close_tag();

			$this->view->close_tag();
		}

		public function execute() {
			if (isset($_GET["order"]) == false) {
				$_SESSION["user_search"] = null;
			}

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($this->model->allow_edit_organisation() == false) {
					$_POST["organisation_id"] = $this->user->organisation_id;
				}

				if ($_POST["submit_button"] == "Save user") {
					/* Fix password
					 */
					if (is_true($_POST["generate"] ?? false)) {
						if (($_POST["password"] = random_string(20)) == false) {
							$this->view->add_message("Error while generating password.");
							$this->show_user_form($_POST);
							return;
						}
					}

					/* Save user
					 */
					if ($this->model->save_okay($_POST) == false) {
						$this->show_user_form($_POST);
					} else if (isset($_POST["id"]) === false) {
						/* Create user
						 */
						if ($this->model->create_user($_POST) === false) {
							$this->view->add_system_warning("Database error while creating user.");
							$this->show_user_form($_POST);
						} else {
							$this->user->log_action("user %s created", $_POST["username"]);
							if (is_true($_POST["generate"] ?? false)) {
								$this->model->send_notification($_POST);
							}
							$this->show_user_overview();
						}
					} else {
						/* Update user
						 */
						$username = $this->model->get_username($_POST["id"]);

						if ($this->model->update_user($_POST) === false) {
							$this->view->add_system_warning("Database error while updating user.");
							$this->show_user_form($_POST);
						} else {
							if (is_true(MENU_PERSONALIZED)) {
								$cache = new \Banshee\Core\cache($this->db, "banshee_menu");
								$cache->store("last_updated", time(), 365 * DAY);
							}

							if ($_POST["username"] == $username) {
								$name = $_POST["id"];
							} else {
								$name = sprintf("%s -> %s", $username, $_POST["username"]);
							}
							$this->user->log_action("user %s updated", $name);
							if (is_true($_POST["generate"] ?? false)) {
								$this->model->send_notification($_POST);
							}
							$this->show_user_overview();
						}
					}
				} else if ($_POST["submit_button"] == "Delete user") {
					/* Delete user
					 */
					$username = $this->model->get_username($_POST["id"]);

					if ($this->model->delete_okay($_POST["id"]) == false) {
						$this->show_user_form($_POST);
					} else if ($this->model->delete_user($_POST["id"]) == false) {
						$this->view->add_system_warning("Database error while deleting user.");
						$this->show_user_form($_POST);
					} else {
						$this->user->log_action("user %s deleted", $username);
						$this->show_user_overview();
					}
				} else if ($_POST["submit_button"] == "search") {
					/* Search
					 */
					$_SESSION["user_search"] = $_POST["search"];
					$this->show_user_overview();
				} else {
					$this->show_user_overview();
				}
			} else if ($this->page->parameter_value(0, "new")) {
				/* Show the user webform
				 */
				$user = array(
					"organisation_id" => $this->user->organisation_id,
					"roles"           => array(USER_ROLE_ID),
					"status"          => USER_STATUS_CHANGEPWD);
				$this->show_user_form($user);
			} else if ($this->page->parameter_value(0, "authenticator") && $this->page->ajax_request) {
				$authenticator = new \Banshee\authenticator;
				$this->view->add_tag("secret", $authenticator->create_secret());
			} else if ($this->page->parameter_numeric(0)) {
				/* Show the user webform
				 */
				if (($user = $this->model->get_user($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "User not found.");
				} else {
					if (empty($user["authenticator_secret"]) == false) {
						$user["authenticator_secret"] = str_repeat("*", strlen($user["authenticator_secret"]));
					}
					$this->show_user_form($user);
				}
			} else {
				/* Show a list of all users
				 */
				$this->show_user_overview();
			}
		}
	}
?>
