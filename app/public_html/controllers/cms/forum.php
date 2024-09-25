<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	require_once("../libraries/helpers/output.php");

	class cms_forum_controller extends Banshee\controller {
		private function show_topic_overview() {
			if (($topic_count = $this->model->count_topics()) === false) {
				$this->view->add_tag("result", "Database error.");
				return false;
			}

			$pagination = new \Banshee\pagination($this->view, "admin_forum", $this->settings->admin_page_size, $topic_count);

			if ($topic_count == 0) {
				$topics = array();
			} else if (($topics = $this->model->get_topics($pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$this->view->open_tag("overview");

			$this->view->open_tag("topics");
			foreach ($topics as $topic) {
				$topic["first"] = date("d-m-Y H:i", $topic["first"]);
				$topic["last"] = date("d-m-Y H:i", $topic["last"]);
				$this->view->record($topic, "topic");
			}
			$this->view->close_tag();

			$pagination->show_browse_links();

			$this->view->close_tag();
		}

		private function show_topic_form($topic) {
			if (($forums = $this->model->get_forums()) === false) {
				$this->view->add_tag("result", "Database error.");
				return false;
			}

			$this->view->open_tag("edit");

			$this->view->open_tag("forums");
			foreach ($forums as $forum) {
				$this->view->add_tag("forum", $forum["title"], array("id" => $forum["id"]));
			}
			$this->view->close_tag();

			$topic["sticky"] = show_boolean($topic["sticky"]);
			$topic["closed"] = show_boolean($topic["closed"]);
			$this->view->record($topic, "topic");
			$this->view->close_tag();
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Save topic") {
					/* Update topic
					 */
					if ($this->model->save_okay($_POST) == false) {
						$this->show_topic_form($_POST);
					} else if ($this->model->update_topic($_POST) === false) {
						$this->view->add_message("Database error while saving topic.");
						$this->show_topic_form($_POST);
					} else {
						$this->user->log_action("forum topic %d updated", $_POST["id"]);
						$this->show_topic_overview();
					}
				} else if ($_POST["submit_button"] == "Delete topic") {
					/* Delete topic
					 */
					if ($this->model->delete_topic($_POST["id"]) == false) {
						$this->view->add_message("Database error while deleting topic.");
						$this->show_topic_form($_POST);
					} else {
						$this->user->log_action("forum topic %d deleted", $_POST["id"]);
						$this->show_topic_overview();
					}
				} else {
					$this->show_topic_overview();
				}
			} else if ($this->page->parameter_numeric(0)) {
				/* Edit existing topic
				 */
				if (($topic = $this->model->get_topic($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "Message not found.");
				} else {
					$this->show_topic_form($topic);
				}
			} else {
				/* Show topic overview
				 */
				$this->show_topic_overview();
			}
		}
	}
?>
