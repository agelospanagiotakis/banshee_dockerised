<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_poll_controller extends Banshee\controller {
		private function show_poll_overview() {
			if (($polls = $this->model->get_polls()) === false) {
				$this->view->add_tag("result", "Database error");
			} else {
				$today = strtotime("today 00:00:00");

				$this->view->open_tag("overview");

				$this->view->open_tag("polls");
				foreach ($polls as $poll) {
					$edit = $poll["begin"] > $today;
					$args = array("edit" => show_boolean($edit));
					if ($edit == false) {
						$args["button"] = $poll["end"] >= $today ? "close" : "delete";
					}
					$poll["begin"] = date("j F Y", $poll["begin"]);
					$poll["end"] = date("j F Y", $poll["end"]);
					$this->view->record($poll, "poll", $args);
				}
				$this->view->close_tag();

				$this->view->close_tag();
			}
		}

		private function show_poll_form($poll) {
			if (isset($poll["id"]) == false) {
				$params = array();
			} else {
				$params = array("id" => $poll["id"]);
			}

			$this->view->add_javascript("webui/jquery-ui.js");
			$this->view->add_javascript("banshee/datepicker.js");

			$this->view->add_css("webui/jquery-ui.css");

			$this->view->open_tag("edit");

			$this->view->open_tag("poll", $params);
			$this->view->add_tag("question", $poll["question"] ?? "");
			$this->view->add_tag("begin", $poll["begin"]);
			$this->view->add_tag("begin_show", date("j F Y", strtotime($poll["begin"])));
			$this->view->add_tag("end", $poll["end"]);
			$this->view->add_tag("end_show", date("j F Y", strtotime($poll["end"])));

			$this->view->open_tag("answers");
			for ($i = 0; $i < $this->settings->poll_max_answers; $i++) {
				$this->view->add_tag("answer", $poll["answers"][$i] ?? "", array("nr" => $i + 1));
			}
			$this->view->close_tag();

			$this->view->close_tag();

			$this->view->close_tag();
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Save poll") {
					/* Save poll
					 */
					if ($this->model->save_okay($_POST) == false) {
						$this->show_poll_form($_POST);
					} else {
						if (isset($_POST["id"]) == false) {
							/* Create poll
							 */
							if ($this->model->create_poll($_POST) == false) {
								$this->view->add_message("Error while creating poll.");
								$this->show_poll_form($_POST);
							} else {
								$this->user->log_action("poll %d created", $this->db->last_insert_id);
								$this->show_poll_overview();
							}
						} else {
							/* Update poll
							 */
							if ($this->model->update_poll($_POST) == false) {
								$this->view->add_message("Error while updating poll.");
								$this->show_poll_form($_POST);
							} else {
								$this->user->log_action("poll %d updated", $_POST["id"]);
								$this->show_poll_overview();
							}
						}
					}
				} else if (($_POST["submit_button"] == "Delete poll") ||
				           ($_POST["submit_button"] == "Delete")) {
					/* Delete poll
					 */
					if ($this->model->delete_poll($_POST["id"]) == false) {
						$this->view->add_system_warming("Error while deleting poll.");
					} else {
						$this->user->log_action("poll %d deleted", $_POST["id"]);
					}
					$this->show_poll_overview();
				} else if ($_POST["submit_button"] == "Close") {
					/* Close poll
					 */
					if ($this->model->close_poll($_POST["id"]) == false) {
						$this->view->add_system_warning("Error while closing poll.");
					} else {
						$this->user->log_action("poll %d closed", $_POST["id"]);
					}
					$this->show_poll_overview();
				} else {
					$this->show_poll_overview();
				}
			} else if ($this->page->parameter_numeric(0)) {
				/* Edit existing poll
				 */
				if (($poll = $this->model->get_poll($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "Poll not found or not available for editing.");
				} else {
					$this->show_poll_form($poll);
				}
			} else if ($this->page->parameter_value(0,"new")) {
				/* Create new poll
				 */
				$poll = array(
					"begin"   => date("Y-m-d", strtotime("next monday")),
					"end"     => date("Y-m-d", strtotime("next monday + 4 weeks - 1 day")),
					"answers" => array());
				$this->show_poll_form($poll);
			} else {
				/* Show poll overview
				 */
				$this->show_poll_overview();
			}
		}
	}
?>
