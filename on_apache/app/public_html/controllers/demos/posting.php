<?php
	class demos_posting_controller extends Banshee\controller {
		public function execute() {
			$this->view->title = "Posting library demo";

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				$input = $_POST["input"];

				$message = new \Banshee\message($input);
				if ($message->is_spam == false) {
					$message->unescaped_output();
					$message->translate_bbcodes();
					$message->translate_smilies();

					$this->view->add_tag("output", $message->content);
				} else {
					$this->view->add_message("Message seen as spam.");
				}

				$this->view->add_tag("input", $input);
			}
		}
	}
?>
