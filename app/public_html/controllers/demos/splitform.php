<?php
	class demos_splitform_controller extends Banshee\splitform_controller {
		protected $back_page = "demos";

		public function prepare_two($data) {
			if ((($data["content"] ?? null) == "") && (($data["name"] ?? null) != "")) {
				$this->model->set_value("content", "Hello ".$data["name"]);
			}
		}

		public function execute() {
			$this->model->default_value("title", "Hello world");

			if ($_SERVER["REQUEST_METHOD"] == "GET") {
				$this->model->reset_form_progress();
			}

			parent::execute();
		}
	}
?>
