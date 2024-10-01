<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_weblog_comment_model extends Banshee\model {
		public function count_comments() {
			$query = "select count(*) as count from weblog_comments c, weblogs w ".
			         "where c.weblog_id=w.id";
			$args = array();

			if ($this->user->is_admin == false) {
				$query .= " and w.user_id=%d";
				array_push($args, $this->user->id);
			}

			if (($result = $this->db->execute($query, $args)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_comments($offset, $limit) {
			$query = "select c.*, w.title as weblog from weblog_comments c, weblogs w ".
			         "where c.weblog_id=w.id";
			$args = array();

			if ($this->user->is_admin == false) {
				$query .= " and w.user_id=%d";
				array_push($args, $this->user->id);
			}

			$query .= " order by timestamp desc limit %d,%d";
			array_push($args, $offset, $limit);

			return $this->db->execute($query, $args);
		}

		public function get_comment($comment_id) {
			$query = "select c.* from weblog_comments c, weblogs w ".
			         "where c.weblog_id=w.id and c.id=%d";
			$args = array($comment_id);

			if ($this->user->is_admin == false) {
				$query .= " and w.user_id=%d";
				array_push($args, $this->user->id);
			}

			if (($result = $this->db->execute($query, $args)) == false) {
				return false;
			}

			return $result[0];
		}

		public function save_okay($comment) {
			$result = true;

			if (trim($comment["author"]) == "") {
				$this->view->add_message("The author can't be empty.");
				$result = false;
			}

			if (trim($comment["content"]) == "") {
				$this->view->add_message("The content can't be empty.");
				$result = false;
			}

			if ($this->get_comment($comment["id"]) == false) {
				$this->view->add_message("Weblog comment not found.");
				$result = false;
			}

			return $result;
		}

		public function update_comment($comment) {
			$keys = array("author", "content");

			return $this->db->update("weblog_comments", $comment["id"], $comment, $keys);
		}

		public function delete_okay($comment) {
			$result = true;

			if ($this->get_comment($comment["id"]) == false) {
				$this->view->add_message("Weblog comment not found.");
				$result = false;
			}

			return $result;
		}

		public function delete_comment($comment_id) {
			return $this->db->delete("weblog_comments", $comment_id);
		}
	}
?>
