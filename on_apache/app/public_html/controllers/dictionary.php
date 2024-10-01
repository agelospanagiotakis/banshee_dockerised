<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class dictionary_controller extends Banshee\controller {
		private function show_letters($letters, $first_letter) {
			$this->view->open_tag("letters", array("selected" => $first_letter));
			foreach ($letters as $letter) {
				$this->view->add_tag("letter", $letter["char"]);
			}
			$this->view->close_tag();
		}

		public function execute() {
			if (($letters = $this->model->get_first_letters()) === false) {
				$this->view->add_tag("result", "Database error");
				return;
			}

			$this->view->description = "Dictionary";

			if ($this->page->parameter_numeric(0)) {
				/* Show word
				 */
				if (($word = $this->model->get_word($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "Unknown word");
					return;
				}

				$this->view->keywords = $word["word"].", dictionary";
				$this->view->title = $word["word"]." - Dictionary";

				$first_letter = strtolower(substr($word["word"], 0, 1));

				$this->view->open_tag("word");
				$this->show_letters($letters, $first_letter);
				$this->view->record($word, "word");
				$this->view->close_tag();
			} else {
				/* Show overview
				 */
				$this->view->keywords = "dictionary";
				$this->view->title = "Dictionary";

				if (valid_input($this->page->parameters[0] ?? null, VALIDATE_NONCAPITALS, 1) == false) {
					$first_letter = $letters[0]["char"];
				} else {
					$first_letter = $this->page->parameters[0];
				}

				if (($words = $this->model->get_words($first_letter)) === false) {
					$this->view->add_tag("result", "Database error.");
					return;
				}

				$this->view->open_tag("overview");
				$this->show_letters($letters, $first_letter);
				$this->view->open_tag("words");
				foreach ($words as $word) {
					$this->view->record($word, "word");
				}
				$this->view->close_tag();
				$this->view->close_tag();
			}
		}
	}
?>
