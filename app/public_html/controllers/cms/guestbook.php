<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	require_once("../libraries/helpers/output.php");

	class cms_guestbook_controller extends Banshee\controller {
		 public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				/* Delete message
				 */
				if ($this->model->delete_message($_POST["id"])) {
					$this->user->log_action("guestbook entry %d deleted", $_POST["id"]);
				}
			}

			if (($message_count = $this->model->count_messages()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			handle_table_sort("adminguestbook_order", array("author", "message", "timestamp", "ip_address"), array("timestamp", "author"));
			$pagination = new \Banshee\pagination($this->view, "admin_guestbook", $this->settings->admin_page_size, $message_count);

			if ($message_count == 0) {
				$guestbook = array();
			} else if (($guestbook = $this->model->get_messages($_SESSION["adminguestbook_order"], $pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$this->view->open_tag("guestbook");

			foreach ($guestbook as $item) {
				$item["message"] = truncate_text($item["message"], 45);
				if ($this->view->mobile) {
					$item["timestamp"] = date("Y-m-d", $item["timestamp"]);
				} else {
					$item["timestamp"] = date("j F Y, H:i", $item["timestamp"]);
				}
				$this->view->record($item, "item");
			}

			$pagination->show_browse_links();

			$this->view->close_tag();
		}
	}
?>
