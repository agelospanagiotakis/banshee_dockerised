<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_newsletter_model extends Banshee\model {
		public function newsletter_okay($info) {
			$result = true;

			if ($info["title"] == "") {
				$this->view->add_message("No title has been entered.");
				$result = false;
			}

			if ($info["content"] == "") {
				$this->view->add_message("No content has been entered.");
				$result = false;
			}

			return $result;
		}

		public function send_newsletter($info) {
			$newsletter = new \Banshee\newsletter($info["title"], $this->settings->newsletter_email, $this->settings->newsletter_name);
			$newsletter->message($info["content"]);

			$query = "select * from subscriptions";
			if (($subscribers = $this->db->execute($query)) === false) {
				return false;
			}

			$chunks = array_chunk($subscribers, $this->settings->newsletter_bcc_size);

			foreach ($chunks as $subscribers) {
				foreach ($subscribers as $subscriber) {
					$newsletter->bcc($subscriber["email"]);
				}

				if ($newsletter->send($this->settings->newsletter_email, $this->settings->newsletter_name) == false) {
					return false;
				}
			}

			return true;
		}

		public function preview_newsletter($info) {
			$newsletter = new \Banshee\newsletter($info["title"], $this->settings->newsletter_email, $this->settings->newsletter_name);
			$newsletter->message($info["content"]);

			return $newsletter->send($this->user->email);
		}
	}
?>
