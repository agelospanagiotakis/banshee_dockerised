<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class mailbox_model extends Banshee\model {
		public function count_new_mail() {
			$query = "select count(*) as count from mailbox where to_user_id=%d and (status & %d = 0)";

			if (($result = $this->db->execute($query, $this->user->id, MAIL_READ)) === false) {
				return 0;
			}

			return (int)$result[0]["count"];
		}

		private function get_mailbox($sent, $archived = null) {
			$query = "select m.id, m.from_user_id, m.subject, UNIX_TIMESTAMP(m.timestamp) as timestamp, m.status, u.fullname as user ".
			         "from mailbox m, users u where ";
			$status = array();

			if ($sent == false) {
				$query .= "m.from_user_id=u.id and m.to_user_id=%d and (m.status & %d = 0) and (m.status & %d = %d)";
				array_push($status, MAIL_DELETED_BY_RECEIVER);
				array_push($status, MAIL_ARCHIVED);
				array_push($status, $archived ? MAIL_ARCHIVED : 0);
			} else {
				$query .= "m.to_user_id=u.id and m.from_user_id=%d and (m.status & %d = 0)";
				array_push($status, MAIL_DELETED_BY_SENDER);
			}

			$query .= "order by timestamp desc";

			return $this->db->execute($query, $this->user->id, $status);
		}

		public function get_inbox() {
			return $this->get_mailbox(false, false);
		}

		public function get_sent() {
			if (($mails = $this->get_mailbox(true)) === false) {
				return false;
			}

			foreach ($mails as $m => $mail) {
				$mails[$m]["status"] |= MAIL_READ;
			}

			return $mails;
		}

		public function get_archive() {
			return $this->get_mailbox(false, true);
		}

		public function get_mail($mail_id) {
			$query = "select m.*, UNIX_TIMESTAMP(m.timestamp) as timestamp, f.fullname as from_user, t.fullname as to_user ".
			         "from mailbox m, users f, users t ".
			         "where m.id=%d and m.from_user_id=f.id and m.to_user_id=t.id and (m.to_user_id=%d or m.from_user_id=%d)";

			if (($result = $this->db->execute($query, $mail_id, $this->user->id, $this->user->id)) == false) {
				return false;
			}
			$mail = $result[0];

			if ($mail["to_user_id"] == $this->user->id) {
				if (($mail["status"] & MAIL_DELETED_BY_RECEIVER) > 0) {
					return false;
				}
				$this->db->update("mailbox", $mail_id, array("status" => $mail["status"] | MAIL_READ));
			} else {
				if (($mail["status"] & MAIL_DELETED_BY_SENDER) > 0) {
					return false;
				}
			}

			return $mail;
		}

		public function get_recipients() {
			$query = "select id, fullname from users where id!=%d and status!=%s";

			return $this->db->execute($query, $this->user->id, USER_STATUS_DISABLED);
		}

		public function get_reply_mail($mail_id) {
			if (($mail = $this->get_mail($mail_id)) == false) {
				return false;
			}

			$mail["subject"] = "Re: ".$mail["subject"];
			$mail["message"] = wordwrap($mail["message"], 50, "\n");
			$mail["message"] = "\n\n\n> ".str_replace("\n", "\n> ", $mail["message"]);

			return $mail;
		}

		public function send_okay($mail) {
			$result = true;

			if (isset($mail["to_user_id"]) == false) {
				$this->view->add_message("Select a recipient.");
				$result = false;
			} else if ($this->db->entry("users", $mail["to_user_id"]) == false) {
				$this->view->add_message("Unknown recipient.");
				$result = false;
			}

			if (trim($mail["subject"]) == "") {
				$this->view->add_message("Empty subject not allowed.");
				$result = false;
			}

			if (trim($mail["message"]) == "") {
				$this->view->add_message("Empty message not allowed.");
				$result = false;
			}

			return $result;
		}

		public function send_mail($mail) {
			$data = array(
				"id"           => null,
				"from_user_id" => (int)$this->user->id,
				"to_user_id"   => (int)$mail["to_user_id"],
				"subject"      => $mail["subject"],
				"message"      => $mail["message"],
				"timestamp"    => null,
				"status"       => MAIL_NEW);

			return $this->db->insert("mailbox", $data) != false;
		}

		public function archive_mail($mail_id) {
			if (($mail = $this->get_mail($mail_id)) == false) {
				return false;
			}

			if ($mail["to_user_id"] != $this->user->id) {
				return false;
			}

			$mail["status"] |= MAIL_ARCHIVED;
			return $this->db->update("mailbox", $mail_id, array("status" => $mail["status"])) != false;
		}

		public function delete_mail($mail_id) {
			if (($mail = $this->get_mail($mail_id)) == false) {
				return false;
			}

			if ($mail["from_user_id"] == $this->user->id) {
				/* Deleted by sender
				 */
				if (($mail["status"] & MAIL_DELETED_BY_RECEIVER) == 0) {
					$mail["status"] |= MAIL_DELETED_BY_SENDER;
					if ($this->db->update("mailbox", $mail_id, array("status" => $mail["status"])) === false) {
						return false;
					}

					return $mail;
				}
			} else {
				/* Deleted by receiver
				 */
				if (($mail["status"] & MAIL_DELETED_BY_SENDER) == 0) {
					$mail["status"] |= MAIL_DELETED_BY_RECEIVER;
					if ($this->db->update("mailbox", $mail_id, array("status" => $mail["status"])) === false) {
						return false;
					}

					return $mail;
				}
			}

			if ($this->db->delete("mailbox", $mail_id) === false) {
				return false;
			}

			return $mail;
		}
	}
?>
