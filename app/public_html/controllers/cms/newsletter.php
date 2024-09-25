<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_newsletter_controller extends Banshee\controller {
		private function start_writing() {
			$newsletter = array();
			$this->show_newsletter_form($newsletter);
		}

		private function show_newsletter_form($newsletter) {
			$this->view->record($newsletter, "newsletter");
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				/* Send newsletter
				 */
				if ($this->model->newsletter_okay($_POST) == false) {
					$this->show_newsletter_form($_POST);
				} else if ($_POST["submit_button"] == "Send newsletter") {
					if ($this->model->send_newsletter($_POST) == false) {
						$this->view->add_message("Error while sending newsletter.");
						$this->show_newsletter_form($_POST);
					} else {
						$this->view->add_system_message("Newsletter has been sent.");
						$this->start_writing();
					}
				} else if ($_POST["submit_button"] == "Preview newsletter") {
					if ($this->model->preview_newsletter($_POST) == false) {
						$this->view->add_message("Error while sending newsletter preview.");
					} else {
						$this->view->add_system_message("Newsletter preview has been sent.");
					}
					$this->show_newsletter_form($_POST);
				} else {
					$this->start_writing();
				}
			} else {
				/* Show newsletter form
				 */
				$this->start_writing();
			}
		}
	}
?>
