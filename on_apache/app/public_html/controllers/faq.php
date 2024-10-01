<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class faq_controller extends Banshee\controller {
		public function execute() {
			$this->view->title = "F.A.Q.";

			if (($sections = $this->model->get_all_sections()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			if (($faqs = $this->model->get_all_faqs()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$this->view->add_javascript("faq.js");

			$this->view->open_tag("overview");

			$this->view->open_tag("sections");
			foreach ($sections as $section) {
				$this->view->add_tag("section", $section["label"], array("id" => $section["id"]));
			}
			$this->view->close_tag();

			$this->view->open_tag("faqs");
			$number = 1;
			foreach ($faqs as $faq) {
				$faq["question"] = ($number++).". ".$faq["question"];
				$this->view->record($faq, "faq");
			}
			$this->view->close_tag();

			$this->view->close_tag();
		}
	}
?>
