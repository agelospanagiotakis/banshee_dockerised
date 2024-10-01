<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class news_model extends Banshee\model {
		public function count_news() {
			$now = date("Y-m-d H:i:s");
			$query = "select count(*) as count from news where timestamp<%s";

			if (($result = $this->db->execute($query, $now)) === false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_news($offset, $limit) {
			$now = date("Y-m-d H:i:s");
			$query = "select *, UNIX_TIMESTAMP(timestamp) as timestamp from news ".
					 "where timestamp<%s order by timestamp desc limit %d,%d";

			return $this->db->execute($query, $now, $offset, $limit);
		}

		public function get_news_item($id) {
			$query = "select * from news where id=%d";

			if ($this->user->access_allowed("cms/news") == false) {
				$query .= " and timestamp<%s";
				$now = date("Y-m-d H:i:s");
			} else {
				$now = null;
			}

			if (($result = $this->db->execute($query, $id, $now)) === false) {
				return false;
			}

			return $result[0];
		}
	}
?>
