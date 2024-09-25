<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_guestbook_model extends Banshee\model {
		public function count_messages() {
			$query = "select count(*) as count from guestbook";

			if (($result = $this->db->execute($query)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_messages($order, $offset, $count) {
			$query = "select *, UNIX_TIMESTAMP(timestamp) as timestamp ".
					 "from guestbook order by %S,%S desc limit %d,%d";

			return $this->db->execute($query, $order, $offset, $count);
		}

		public function delete_message($message_id) {
			$this->db->delete("guestbook", $message_id);
		}
	}
?>
