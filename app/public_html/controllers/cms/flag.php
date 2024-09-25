<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_flag_controller extends Banshee\tablemanager_controller {
		protected $name = "Flag";
		protected $back = "cms";
		protected $icon = "flags.png";
		protected $page_size = 25;
		protected $pagination_links = 7;
		protected $pagination_step = 1;
		protected $foreign_null = "---";
		protected $browsing = "pagination";

		protected function show_item_form($item) {
			$this->view->add_javascript("cms/flag.js");

			parent::show_item_form($item);
		}

		private function send_flags() {
			if (($flags = $this->model->get_flags($_GET["module"])) === false) {
				return;
			}

			$this->view->open_tag("flags");
			foreach ($flags as $flag) {
				$this->view->add_tag("flag", $flag, array("selected" => "no"));
			}
			$this->view->close_tag();
		}

		public function execute() {
			if ($this->page->ajax_request) {
				$this->send_flags();
			} else if (count($this->model->module_flags) == 0) {
				$this->view->open_tag("tablemanager");
				$this->view->add_tag("result", "No flags are available.", array("url" => "cms"));
				$this->view->close_tag();
			} else {
				parent::execute();
			}
		}
	}
?>
