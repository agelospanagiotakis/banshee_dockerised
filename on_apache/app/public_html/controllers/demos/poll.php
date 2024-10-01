<?php
	class demos_poll_controller extends Banshee\controller {
		public function execute() {
			$this->view->title = "Poll demo";

			$poll = new \Banshee\poll($this->db, $this->view, $this->settings);

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				$poll->vote($_POST["vote"] ?? null);
			}

			$poll->add_to_view();
		}
	}
?>
