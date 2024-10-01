<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_forum_model extends Banshee\model {
		public function count_topics() {
			$query = "select count(*) as count from forum_topics";

			if (($result = $this->db->execute($query)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_topics($offset, $count) {
			$query = "select *, (select UNIX_TIMESTAMP(timestamp) from forum_messages ".
			         "where forum_topic_id=t.id order by timestamp desc limit 1) as last, ".
			         "(select UNIX_TIMESTAMP(timestamp) from forum_messages ".
			         "where forum_topic_id=t.id order by timestamp limit 1) as first, ".
			         "(select count(*) from forum_messages where forum_topic_id=t.id) as messages, ".
			         "(select fullname from forum_messages m, users u where m.user_id=u.id ".
			         "and forum_topic_id=t.id order by timestamp limit 1) as user, ".
			         "(select username from forum_messages m where forum_topic_id=t.id ".
			         "order by timestamp limit 1) as visitor ".
					 "from forum_topics t order by first desc limit %d,%d";

			$topics = $this->db->execute($query, $offset, $count);

			foreach ($topics as $t => $topic) {
				$topics[$t]["author"] = ($topic["visitor"] != "") ? $topic["visitor"] : $topic["user"];
				unset($topics[$t]["visitor"]);
				unset($topics[$t]["user"]);
			}

			return $topics;
		}

		public function get_topic($topic_id) {
			return $this->db->entry("forum_topics", $topic_id);
		}

		public function get_forums() {
			$query = "select id, title from forums order by %S";

			return $this->db->execute($query, "order");
		}

		public function save_okay($topic) {
			$result = true;

			if (trim($topic["subject"]) == "") {
				$this->view->add_topic("Empty subject not allowed.");
				$result = false;
			}

			return $result;
		}

		public function update_topic($topic) {
			$keys = array("forum_id", "subject", "sticky", "closed");

			$topic["sticky"] = is_true($topic["sticky"] ?? false) ? YES : NO;
			$topic["closed"] = is_true($topic["closed"] ?? false) ? YES : NO;

			return $this->db->update("forum_topics", $topic["id"], $topic, $keys) !== false;
		}

		public function delete_topic($topic_id) {
			$queries = array(
				array("delete from forum_last_view where forum_topic_id=%d", $topic_id),
				array("delete from forum_messages where forum_topic_id=%d", $topic_id),
				array("delete from forum_topics where id=%d", $topic_id));

			return $this->db->transaction($queries);
		}
	}
?>
