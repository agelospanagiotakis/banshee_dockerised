<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class guestbook_controller extends Banshee\controller {
		private function show_guestbook_form($message) {
			$this->view->record($message);
		}

		public function execute() {
			$this->view->description = "Guestbook";
			$this->view->keywords = "guestbook";
			$this->view->title = "Guestbook";
			$skip_sign_link = false;

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($this->model->message_okay($_POST) == false) {
					$this->show_guestbook_form($_POST);
				} else if ($this->model->save_message($_POST) == false) {
					$this->view->add_message("Database errors while saving message.");
					$this->show_guestbook_form($_POST);
				} else {
					$skip_sign_link = true;
				}
			}

			if (($message_count = $this->model->count_messages()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$pagination = new \Banshee\pagination($this->view, "guestbook", $this->settings->guestbook_page_size, $message_count);

			if (($guestbook = $this->model->get_messages($pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Database error.");
			} else {
				$this->view->open_tag("guestbook", array("skip_sign_link" => show_boolean($skip_sign_link)));

				foreach ($guestbook as $item) {
					$item["timestamp"] = date_string("j F Y, H:i", $item["timestamp"]);
					$message = new \Banshee\message($item["message"]);
					$item["message"] = $message->unescaped_output();
					unset($item["ip_address"]);
					$this->view->record($item, "item");
				}

				$pagination->show_browse_links(7, 3);

				$this->view->close_tag();
			}
		}
	}
?>
