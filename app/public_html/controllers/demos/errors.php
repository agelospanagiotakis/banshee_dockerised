<?php
	class demos_errors_controller extends Banshee\controller {
		public function execute() {
			$this->view->title = "Error demo";

			print "This is an error message.\n";
			print "This is an error message.\n";
			print "This is an error message.\n";

			$this->view->add_system_message("This is a system message.");
			$this->view->add_system_warning("This is a system warning.");
		}
	}
?>
