<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_action_controller extends Banshee\controller {
		public function execute() {
			$offset = $this->page->parameter_numeric(0) ? $this->page->parameters[0] : 0;

			$admin_actionlog_size = $this->model->get_log_size();
			$pagination = new \Banshee\pagination($this->view, "admin_actionlog", $this->settings->admin_page_size, $admin_actionlog_size);

			if ($pagination->offset === null) {
				$log = array();
			} else if (($log = $this->model->get_action_log($pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Error reading action log.");
				return;
			}

			$users = array($this->user->id => $this->user->username);

			$this->view->open_tag("log");

			$this->view->open_tag("list");
			foreach ($log as $entry) {
				$parts = explode(":", $entry["user_id"]);
				$user_id = array_shift($parts);
				$switch_id = array_shift($parts);

				if (($user_id != "-") && isset($users[$user_id]) == false) {
					if (($user = $this->model->get_user($user_id)) != false) {
						$users[$user_id] = $user["username"] ?? "?";
					}
				}

				if (isset($switch_id) && isset($users[$switch_id]) == false) {
					if (($switch = $this->model->get_user($switch_id)) != false) {
						$users[$switch_id] = $switch["username"] ?? "?";
					}
				}

				$entry["username"] = isset($users[$user_id]) ? $users[$user_id] : "-";
				$entry["switch"] = isset($users[$switch_id]) ? $users[$switch_id] : "-";

				$this->view->record($entry, "entry");
			}
			$this->view->close_tag();

			$pagination->show_browse_links();

			$this->view->close_tag();
		}
	}
?>
