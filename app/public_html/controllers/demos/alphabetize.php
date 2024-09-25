<?php
	class demos_alphabetize_controller extends Banshee\controller {
		public function execute() {
			$alphabetize = new \Banshee\alphabetize($this->view, "demo");
			$words = $this->model->get_words($alphabetize->char);

			$this->view->open_tag("words");
			foreach ($words as $word) {
				$this->view->add_tag("word", $word);
			}
			$this->view->close_tag();

			$alphabetize->show_browse_links();
		}
	}
?>
