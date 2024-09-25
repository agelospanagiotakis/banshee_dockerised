<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_weblog_comment_controller extends Banshee\controller {
		private function show_overview() {
			if (($comment_count = $this->model->count_comments()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$pagination = new \Banshee\pagination($this->view, "comments", $this->settings->admin_page_size, $comment_count);

			if ($comment_count == 0) {
				$comments = array();
			} else if (($comments = $this->model->get_comments($pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$this->view->open_tag("overview");

			$this->view->open_tag("comments");
			foreach ($comments as $comment) {
				$this->view->record($comment, "comment");
			}
			$this->view->close_tag();

			$pagination->show_browse_links();

			$this->view->close_tag();
		}

		private function show_comment_form($comment) {
			$this->view->open_tag("edit");
			$this->view->record($comment, "comment");
			$this->view->close_tag();
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Save comment") {
					/* Save comment
					 */
					if ($this->model->save_okay($_POST) == false) {
						$this->show_comment_form($_POST);
					} else {
						/* Update comment
						 */
						if ($this->model->update_comment($_POST) === false) {
							$this->view->add_message("Error updating comment.");
							$this->show_comment_form($_POST);
						} else {
							$this->user->log_action("comment %d updated", $_POST["id"]);
							$this->show_overview();
						}
					}
				} else if ($_POST["submit_button"] == "Delete comment") {
					/* Delete comment
					 */
					if ($this->model->delete_okay($_POST) == false) {
						$this->show_comment_form($_POST);
					} else if ($this->model->delete_comment($_POST["id"]) === false) {
						$this->view->add_message("Error deleting comment.");
						$this->show_comment_form($_POST);
					} else {
						$this->user->log_action("comment %d deleted", $_POST["id"]);
						$this->show_overview();
					}
				} else {
					$this->show_overview();
				}
			} else if ($this->page->parameter_numeric(0)) {
				/* Edit comment
				 */
				if (($comment = $this->model->get_comment($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "Comment not found.");
				} else {
					$this->show_comment_form($comment);
				}
			} else {
				/* Show overview
				 */
				$this->show_overview();
			}
		}
	}
?>
