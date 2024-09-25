<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	class poll {
		private $db = null;
		private $view = null;
		private $settings = null;

		/* Constructor
		 *
		 * INPUT:  object database, object view, object settings
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($db, $view, $settings) {
			$this->db = $db;
			$this->view = $view;
			$this->settings = $settings;
		}

		/* Get active poll
		 *
		 * INPUT:  -
		 * OUTPUT: array( poll )
		 * ERROR:  false
		 */
		private function get_active_poll() {
			$now = date("Y-m-d");
			$query = "select *, UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(end) as end ".
			         "from polls where begin<=%s and end>=%s order by begin desc limit 1";
			if (($result = $this->db->execute($query, $now, $now)) == false) {
				return false;
			}

			return $result[0];
		}

		/* Determine if user is allowed to vote
		 *
		 * INPUT:  int poll id
		 * OUTPUT: boolean user may vote
		 * ERROR:  -
		 */
		public function user_may_vote($poll_id) {
			$banned_ips = explode(",", $this->settings->poll_bans);

			foreach ($banned_ips as $banned_ip) {
				if (($banned_ip = trim($banned_ip)) == "") {
					continue;
				}
				if ($_SERVER["REMOTE_ADDR"] == $banned_ip) {
					return false;
				}
			}

			if (isset($_COOKIE["last_poll_id"])) {
				if ((int)$poll_id <= $_COOKIE["last_poll_id"]) {
					return false;
				}
			}

			return true;
		}

		/* Cast vote
		 *
		 * INPUT:  int answer identifier
		 * OUTPUT: true
		 * ERROR:  false
		 */
		public function vote($answer) {
			if ($_POST["submit_button"] != "Vote") {
				return false;
			}

			if ($answer == null) {
				return false;
			}

			$_SERVER["REQUEST_METHOD"] = "GET";

			if (valid_input($answer, VALIDATE_NUMBERS, VALIDATE_NONEMPTY) == false) {
				return false;
			}
			if (($poll = $this->get_active_poll()) == false) {
				return false;
			}

			$today = strtotime("today 00:00:00");
			if ($poll["end"] < $today) {
				return false;
			}

			if ($this->user_may_vote($poll["id"]) == false) {
				return false;
			}

			$query = "select * from poll_answers where poll_id=%d order by answer";
			if (($answers = $this->db->execute($query, $poll["id"])) == false) {
				return false;
			}

			$answer = (int)$answer;
			if ($answer >= count($answers)) {
				return false;
			}
			$answer_id = $answers[$answer]["id"];

			setcookie("last_poll_id", (int)$poll["id"], time() + 100 * DAY, "/");
			$_COOKIE["last_poll_id"] = (int)$poll["id"];

			/* Log selected item
			 */
			$logfile = new logfile("poll");
			$logfile->add_entry($poll["id"]."|".$answer);

			$query = "update poll_answers set votes=votes+1 where id=%d";

			return $this->db->query($query, $answer_id) != false;
		}

		/* Add poll to XML output
		 *
		 * INPUT:  -
		 * OUTPUT: true
		 * ERROR:  false
		 */
		public function add_to_view() {
			$this->view->add_css("banshee/poll.css");

			if (($poll = $this->get_active_poll()) == false) {
				return false;
			}

			$today = strtotime("today 00:00:00");
			$poll_open = ($poll["end"] >= $today) && $this->user_may_vote($poll["id"]);

			$this->view->open_tag("active_poll", array("can_vote" => show_boolean($poll_open)));
			$this->view->add_tag("question", $poll["question"]);
			$this->view->add_tag("end_date", date_string("d F", $poll["end"]));

			$query = "select * from poll_answers where poll_id=%d order by answer";
			if (($answers = $this->db->execute($query, $poll["id"])) != false) {
				if ($poll_open == false) {
					$votes = 0;
					foreach ($answers as $answer) {
						$votes += (int)$answer["votes"];
					}
				}

				$this->view->open_tag("answers", $poll_open ? array() : array("votes" => $votes));
				$poll_id = 0;
				foreach ($answers as $answer) {
					if ($poll_open) {
						$this->view->add_tag("answer", $answer["answer"], array("id" => $poll_id++));
					} else {
						unset($answer["poll_id"]);
						$answer["percentage"] = ($votes > 0) ? round(100 * (int)$answer["votes"] / $votes) : 0;
						$this->view->record($answer, "answer");
					}
				}
				$this->view->close_tag();
			}

			$this->view->close_tag();

			return true;
		}
	}
?>
