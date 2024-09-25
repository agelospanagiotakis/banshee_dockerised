<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class mailbox_controller extends Banshee\controller {
		private function show_mails($mails, $column) {
			$new = $this->model->count_new_mail();

			$this->view->open_tag("mailbox", array("column" => $column, "new" => $new));

			foreach ($mails as $mail) {
				if ($this->view->mobile) {
					$mail["timestamp"] = date_string("Y-m-d", $mail["timestamp"]);
				} else {
					$mail["timestamp"] = date_string("j M Y H:i:s", $mail["timestamp"]);
				}
				$mail["read"] = (($mail["status"] & MAIL_READ) > 0) ? "read" : "unread";
				$this->view->record($mail, "mail");
			}

			$this->view->close_tag();
		}

		private function show_inbox() {
			if (($mails = $this->model->get_inbox()) === false) {
				$this->view->add_tag("result", "Error reading mailbox.");
			} else {
				$this->show_mails($mails, "From");
			}
		}

		private function show_sent() {
			if (($mails = $this->model->get_sent()) === false) {
				$this->view->add_tag("result", "Error reading sent.");
			} else {
				$this->show_mails($mails, "To");
			}
		}

		private function show_archive() {
			if (($mails = $this->model->get_archive()) === false) {
				$this->view->add_tag("result", "Error reading archive.");
			} else {
				$this->show_mails($mails, "From");
			}
		}

		private function show_mail_folder() {
			if ($this->page->parameter_value(0, "sent")) {
				$this->show_sent();
			} else if ($this->page->parameter_value(0, "archive")) {
				$this->show_archive();
			} else {
				$this->show_inbox();
			}
		}

		private function show_mail($mail) {
			$message = new \Banshee\message($mail["message"]);
			$mail["message"] = $message->unescaped_output();

			if ($mail["from_user_id"] == $this->user->id) {
				$folder = "/sent";
			} else if (($mail["status"] & MAIL_ARCHIVED) > 0) {
				$folder = "/archive";
			} else {
				$folder = "";
			}

			$mail["timestamp"] = date_string("l, j F Y H:i:s", $mail["timestamp"]);
			$mail["archived"] = show_boolean(($mail["status"] & MAIL_ARCHIVED) > 0);

			$actions = show_boolean($mail["to_user_id"] == $this->user->id);
			$this->view->record($mail, "mail", array("actions" => $actions, "folder" => $folder));
		}

		private function write_mail($mail) {
			if (($recipients = $this->model->get_recipients()) === false) {
				$this->view->add_tag("result", "Error fetching recipient list.");
				return;
			}

			$this->view->open_tag("write");

			$this->view->open_tag("recipients");
			foreach ($recipients as $recipient) {
				$this->view->add_tag("recipient", $recipient["fullname"], array("id" => $recipient["id"]));
			}
			$this->view->close_tag();

			$this->view->record($mail, "mail");

			$this->view->close_tag();
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Send mail") {
					/* Send mail
					 */
					if ($this->model->send_okay($_POST) == false) {
						$this->write_mail($_POST);
					} else if ($this->model->send_mail($_POST) == false) {
						$this->view->add_message("Error sending mail.");
						$this->write_mail($_POST);
					} else {
						$this->view->add_system_message("Mail has been sent.");
						$this->show_inbox();
						$this->user->log_action("mail %d sent to %d", $this->db->last_insert_id, $_POST["to_user_id"]);
					}
				} else if ($_POST["submit_button"] == "Archive mail") {
					/* Archive mail
					 */
					if ($this->model->archive_mail($_POST["id"]) == false) {
						$this->view->add_system_warning("Error archiving mail.");
					} else {
						$this->user->log_action("mail %d archived", $_POST["id"]);
					}

					$this->show_inbox();
				} else if ($_POST["submit_button"] == "Delete mail") {
					/* Delete mail
					 */
					if (($mail = $this->model->delete_mail($_POST["id"])) === false) {
						$this->view->add_system_warning("Error deleting mail.");
					} else {
						$this->user->log_action("mail %d deleted", $_POST["id"]);
					}

					$this->show_mail_folder();
				}
			} else if ($this->page->parameter_numeric(0)) {
				/* Show mail message
				 */
				if (($mail = $this->model->get_mail($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "Mail not found.");
				} else {
					$this->show_mail($mail);
				}
			} else if ($this->page->parameter_value(0, "new")) {
				/* New mail
				 */
				$mail = array();
				$this->write_mail($mail);
			} else if ($this->page->parameter_value(0, "reply")) {
				/* Reply
				 */
				if (($mail = $this->model->get_reply_mail($this->page->parameters[1])) == false) {
					$this->view->add_tag("result", "Error replying to mail.");
				} else {
					$this->write_mail($mail);
				}
			} else {
				$this->show_mail_folder();
			}
		}
	}
?>
