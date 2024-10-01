<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class poll_model extends Banshee\model {
		public function get_active_poll_id() {
			$now = date("Y-m-d H:i:s");
			$query = "select *, UNIX_TIMESTAMP(begin) as begin, UNIX_TIMESTAMP(end) as end ".
					 "from polls where begin<=%s and end>%s order by begin desc limit 1";
			if (($result = $this->db->execute($query, $now, $now)) == false) {
				return false;
			}

			return $result[0]["id"];
		}

		public function get_polls() {
			$query = "select * from polls where begin<=%s order by begin desc";

			return $this->db->execute($query, date("Y-m-d H:i:s"));
		}

		public function get_poll($poll_id) {
			if ($poll_id == $this->get_active_poll_id()) {
				return false;
			}

			if (($poll = $this->db->entry("polls", $poll_id)) == false) {
				return false;
			}

			if (strtotime($poll["begin"]) > time()) {
				return false;
			}

			$query = "select * from poll_answers where poll_id=%d";
			$poll["answers"] = $this->db->execute($query, $poll["id"]);

			return $poll;
		}
	}
?>
