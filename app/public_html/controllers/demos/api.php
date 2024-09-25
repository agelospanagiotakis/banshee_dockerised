<?php
	class demos_api_controller extends Banshee\api_controller {
		public function get() {
			$this->view->add_tag("demo", "api");
		}

		public function get_0() {
			$this->view->add_tag("demo", "test");
		}

		public function post() {
			$this->view->add_tag("result", "Hello ".$_POST["name"]);
		}
	}
?>
