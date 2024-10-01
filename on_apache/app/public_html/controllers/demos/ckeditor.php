<?php
	class demos_ckeditor_controller extends Banshee\controller {
		public function execute() {
			$this->view->title = "CKEditor demo";

			if (is_false(USE_CKEDITOR)) {
				$this->view->add_tag("result", "The CKEditor is not enabled.", array("url" => "demos"));
			} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
				$this->view->open_tag("submit");
				$this->view->add_tag("editor", $_POST["editor"]);
				$this->view->close_tag();
			} else {
				$this->view->start_ckeditor();
				$this->view->add_tag("edit");
			}
		}
	}
?>
