<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	require_once("../libraries/helpers/output.php");

	class cms_faq_controller extends Banshee\controller {
		public function show_faq_overview() {
			if (($sections = $this->model->get_all_sections()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			} else if (($faqs = $this->model->get_all_faqs()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$this->view->open_tag("overview");

			$this->view->open_tag("sections");
			foreach ($sections as $section) {
				$this->view->add_tag("section", $section["label"], array("id" => $section["id"]));
			}
			$this->view->close_tag();

			$this->view->open_tag("faqs");
			foreach ($faqs as $faq) {
				$faq["question"] = truncate_text($faq["question"], 140);
				$this->view->record($faq, "faq");
			}
			$this->view->close_tag();

			$this->view->close_tag();
		}

		public function show_faq_form($faq) {
			if (($sections = $this->model->get_all_sections()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			if (isset($faq["select"]) == false) {
				$faq["select"] = count($sections) == 0 ? "new" : "existing";
			}

			$this->view->start_ckeditor();
			$this->view->add_javascript("cms/faq.js");

			$this->view->open_tag("edit");

			$this->view->open_tag("sections");
			foreach ($sections as $section) {
				$this->view->add_tag("section", $section["label"], array("id" => $section["id"]));
			}
			$this->view->close_tag();

			$this->view->record($faq, "faq");

			$this->view->close_tag();
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Save FAQ") {
					/* Save FAQ
					 */
					if ($this->model->save_okay($_POST) == false) {
						$this->show_faq_form($_POST);
					} else if (isset($_POST["id"]) === false) {
						/* Create FAQ
						 */
						if ($this->model->create_faq($_POST) == false) {
							$this->view->add_message("Error while creating F.A.Q.");
							$this->show_faq_form($_POST);
						} else {
							$this->user->log_action("faq %d created", $this->db->last_insert_id);
							$this->show_faq_overview();
						}
					} else {
						/* Update FAQ
						 */
						if ($this->model->update_faq($_POST) == false) {
							$this->view->add_message("Error while updating F.A.Q.");
							$this->show_faq_form($_POST);
						} else {
							$this->user->log_action("faq %d updated", $_POST["id"]);
							$this->show_faq_overview();
						}
					}
				} else if ($_POST["submit_button"] == "Delete FAQ") {
					/* Delete FAQ
					 */
					if ($this->model->delete_faq($_POST["id"]) == false) {
						$this->view->add_message("Error while deleting F.A.Q.");
						$this->show_faq_form($_POST);
					} else {
						$this->user->log_action("faq %d deleted", $_POST["id"]);
						$this->show_faq_overview();
					}
				} else {
					$this->show_faq_overview();
				}
			} else if ($this->page->parameter_value(0, "new")) {
				/* New FAQ
				 */
				$faq = array("section" => 1);
				$this->show_faq_form($faq);
			} else if ($this->page->parameter_numeric(0)) {
				/* Edit existing FAQ
				 */
				if (($faq = $this->model->get_faq($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "FAQ not found.");
				} else {
					$this->show_faq_form($faq);
				}
			} else {
				/* FAQ overview
				 */
				$this->show_faq_overview();
			}
		}
	}
?>
