<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_dictionary_controller extends Banshee\controller {
		private function show_dictionary_overview() {
			if (($word_count = $this->model->count_words()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$pagination = new \Banshee\pagination($this->view, "admin_dictionary", $this->settings->admin_page_size, $word_count);

			if ($word_count == 0) {
				$words = array();
			} else if (($words = $this->model->get_words($pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$this->view->open_tag("overview");

			$this->view->open_tag("words");
			foreach ($words as $word) {
				$this->view->record($word, "word");
			}
			$this->view->close_tag();

			$pagination->show_browse_links();

			$this->view->close_tag();
		}

		private function show_word_form($word) {
			if (isset($word["id"]) != false) {
				$letter = strtolower($word["word"][0]);
			}

			$this->view->start_ckeditor();

			$this->view->record($word, "edit");
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Save word") {
					/* Save word
					 */
					if ($this->model->save_okay($_POST) == false) {
						$this->show_word_form($_POST);
					} else if (isset($_POST["id"]) == false) {
						if ($this->model->create_word($_POST) == false) {
							$this->view->add_message("Database error while creating word.");
							$this->show_word_form($_POST);
						} else {
							$this->user->log_action("dictionary word %d created", $this->db->last_insert_id);
							$this->show_dictionary_overview();
						}
					} else {
						if ($this->model->update_word($_POST) == false) {
							$this->view->add_message("Database error while updating word.");
							$this->show_word_form($_POST);
						} else {
							$this->user->log_action("dictionary word %d updated", $_POST["id"]);
							$this->show_dictionary_overview();
						}
					}
				} else if ($_POST["submit_button"] == "Delete word") {
					/* Delete word
					 */
					if ($this->model->delete_word($_POST["id"]) == false) {
						$this->view->add_tag("result", "Error while deleting word.");
					} else {
						$this->user->log_action("dictionary word %d deleted", $_POST["id"]);
						$this->show_dictionary_overview();
					}
				} else {
					$this->show_dictionary_overview();
				}
			} else if ($this->page->parameter_value(0, "new")) {
				/* New word
				 */
				$word = array();
				$this->show_word_form($word);
			} else if ($this->page->parameter_numeric(0)) {
				/* Edit word
				 */
				if (($word = $this->model->get_word($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "Word not found.");
				} else {
					$this->show_word_form($word);
				}
			} else {
				/* Show dictionary overview
				 */
				$this->show_dictionary_overview();
			}
		}
	}
?>
