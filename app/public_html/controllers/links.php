<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class links_controller extends Banshee\controller {
		public function execute() {
			$this->view->title = "Links";
			$this->view->keywords = "links";
			$this->view->description = "Links naar websites over privacy";

			if (($categories = $this->model->get_links()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			foreach ($categories as $category) {
				$this->view->open_tag("links");

				$this->view->add_tag("category", $category["category"]);
				$this->view->add_tag("description", $category["description"]);

				foreach ($category["links"] as $link) {
					$this->view->record($link, "link");
				}

				$this->view->close_tag();
			}
		}
	}
?>
