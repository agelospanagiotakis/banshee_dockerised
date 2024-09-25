<?php
	class demos_windowframe_controller extends Banshee\controller {
		protected $prevent_repost = false;

		public function execute() {
			$this->view->add_javascript("banshee/jquery.windowframe.js");
			$this->view->add_javascript("demos/windowframe.js");
		}
	}
?>
