<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_role_controller extends Banshee\controller {
		public function show_role_overview() {
			if (($roles = $this->model->get_all_roles()) === false) {
				$this->view->add_tag("result", "Database error.");
			} else {
				$this->view->open_tag("overview");

				$this->view->open_tag("roles");
				foreach ($roles as $role) {
					$this->view->add_tag("role", $role["name"], array("id" => $role["id"], "users" => $role["users"]));
				}
				$this->view->close_tag();

				$this->view->close_tag();
			}
		}

		public function show_role_form($role) {
			$params = array(
				"non_admins" => show_boolean($role["non_admins"] ?? null),
				"editable"   => show_boolean(($role["id"] ?? null) != ADMIN_ROLE_ID));

			if (isset($role["id"])) {
				$params["id"] = $role["id"];
			}

			if (($pages = $this->model->get_restricted_pages()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}
			sort($pages);

			$this->view->open_tag("edit");

			/* Roles
			 */
			$this->view->add_tag("role", $role["name"] ?? "", $params);
			$this->view->open_tag("pages");
			foreach ($pages as $page) {
				$this->view->add_tag("page", $page, array("value" => $role[$page] ?? 0));
			}
			$this->view->close_tag();

			$this->view->open_tag("members");
			if (isset($role["id"])) {
				if (($users = $this->model->get_role_members($role["id"])) !== false) {
					foreach ($users as $user) {
						$this->view->open_tag("member", array("id" => $user["id"]));
						$this->view->add_tag("fullname", $user["fullname"]);
						$this->view->add_tag("email", $user["email"]);
						$this->view->close_tag();
					}
				}
			}
			$this->view->close_tag();

			$this->view->close_tag();
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Save role") {
					/* Save role
					 */
					if ($this->model->save_okay($_POST) == false) {
						$this->show_role_form($_POST);
					} else if (isset($_POST["id"]) == false) {
						/* Create role
						 */
						if ($this->model->create_role($_POST) === false) {
							$this->view->add_message("Database error while creating role.");
							$this->show_role_form($_POST);
						} else {
							$this->user->log_action("role %d created", $this->db->last_insert_id);
							$this->show_role_overview();
						}
					} else {
						/* Update role
						 */
						if ($this->model->update_role($_POST) === false) {
							$this->view->add_message("Database error while updating role.");
							$this->show_role_form($_POST);
						} else {
							if (is_true(MENU_PERSONALIZED)) {
								$cache = new \Banshee\Core\cache($this->db, "banshee_menu");
								$cache->store("last_updated", time(), 365 * DAY);
							}

							$this->user->log_action("role %d updated", $_POST["id"]);
							$this->show_role_overview();
						}
					}
				} else if ($_POST["submit_button"] == "Delete role") {
					/* Delete role
					 */
					if ($this->model->delete_okay($_POST) == false) {
						$this->view->add_tag("result", "This role cannot be deleted.");
					} else if ($this->model->delete_role($_POST["id"]) == false) {
						$this->view->add_tag("result", "Database error while deleting role.");
					} else {
						$this->user->log_action("role %d deleted", $_POST["id"]);
						$this->show_role_overview();
					}
				} else {
					$this->show_role_overview();
				}
			} else if ($this->page->parameter_value(0, "new")) {
				/* Show the role webform
				 */
				$role = array("non_admins" => true, "account" => true);
				$this->show_role_form($role);
			} else if ($this->page->parameter_numeric(0)) {
				/* Show the role webform
				 */
				if (($role = $this->model->get_role($this->page->parameters[0])) != false) {
					$this->show_role_form($role);
				} else {
					$this->view->add_tag("result", "Role not found.");
				}
			} else {
				/* Show a list of all roles
				 */
				$this->show_role_overview();
			}
		}
	}
?>
