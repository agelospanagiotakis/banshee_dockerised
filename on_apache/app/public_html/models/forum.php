<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class forum_model extends Banshee\model {
		private function has_right_role($forum) {
			if ($this->user->is_admin) {
				return true;
			}

			if ($forum["required_role_id"] == null) {
				return true;
			}

			$roles = array((int)$forum["required_role_id"]);
			if ($this->settings->forum_maintainers != null) {
				array_push($roles, $this->settings->forum_maintainers);
			}

			return $this->user->has_role($roles);
		}

		public function get_forums() {
			$query = "select *,(select count(*) from forum_topics where forum_id=f.id) as topics ".
					 "from forums f order by %S ";
			if (($forums = $this->db->execute($query, "order")) === false) {
				return false;
			}

			if ($this->user->logged_in) {
				$query = "select distinct t.id from forum_topics t ".
				         "left join forum_last_view l on (l.forum_topic_id=t.id or l.forum_topic_id is null) ".
				         "where l.user_id=%d and t.forum_id=%d and last_view>=".
				         "(select max(timestamp) from forum_messages where forum_topic_id=t.id)";

				$total_unread = 0;
			}

			$section = "";
			$result = array();
			foreach ($forums as $forum) {
				if ($forum["section"] != "") {
					$section = $forum["section"];
				}

				if (is_true($forum["private"]) && is_false($this->user->logged_in)) {
					continue;
				}

				if ($this->user->logged_in) {
					if ($this->has_right_role($forum) == false) {
						continue;
					}

					if (($read = $this->db->execute($query, $this->user->id, $forum["id"])) !== false) {
						$forum["unread"] = $forum["topics"] - count($read);

						$total_unread += $forum["unread"];
					}
				}

				if ($forum["section"] == "") {
					$forum["section"] = $section;
				}
				$section = "";

				array_push($result, $forum);
			}

			if ($this->user->logged_in) {
				if ($total_unread == 0) {
					$this->mark_all_read();
				}
			}

			return $result;
		}

		private function get_forum_record($forum_id) {
			$query = "select * from forums where id=%d";

			if (($result = $this->db->execute($query, $forum_id)) == false) {
				return false;
			}
			$forum = $result[0];

			if ($forum["section"] == "") {
				$query = "select section from forums where section!=%s and %S<%d order by %S desc limit 1";
				if (($section = $this->db->execute($query, "", "order", $forum["order"], "order")) != false) {
					$forum["section"] = $section[0]["section"];
				}
			}

			if ($this->has_right_role($forum) == false) {
				return false;
			}

			if (is_false($this->user->logged_in) && is_false($forum["private"] == false)) {
				return false;
			}

			return $forum;
		}

		public function get_forum($forum_id, $offset, $limit) {
			if (($forum = $this->get_forum_record($forum_id)) == false) {
				return false;
			}

			if ($limit == 0) {
				$forum["topics"] = array();
				return $forum;
			}

			$query = "select *, (select UNIX_TIMESTAMP(timestamp) from forum_messages ".
			         "where forum_topic_id=t.id order by timestamp desc limit 1) as timestamp, ".
			         "(select fullname from forum_messages m, users u where m.user_id=u.id ".
			         "and forum_topic_id=t.id order by timestamp limit 1) as user, ".
			         "(select username from forum_messages m where forum_topic_id=t.id ".
			         "order by timestamp limit 1) as visitor, ".
			         "(select count(*) from forum_messages where forum_topic_id=t.id) as messages ".
			         "from forum_topics t where forum_id=%d order by sticky desc, timestamp desc limit %d,%d";

			if (($topics = $this->db->execute($query, $forum_id, $offset, $limit)) === false) {
				return false;
			}

			foreach ($topics as $t => $topic) {
				if ($this->user->logged_in) {
					$topics[$t]["last_view"] = $this->last_topic_view($topic["id"]);
				}
				$topics[$t]["starter"] = ($topic["visitor"] != "") ? $topic["visitor"] : $topic["user"];
				unset($topics[$t]["visitor"]);
				unset($topics[$t]["user"]);
			}

			$forum["topics"] = $topics;

			return $forum;
		}

		private function is_private_forum($forum_id) {
			$query = "select private from forums where id=%d";

			if (($result = $this->db->execute($query, $forum_id)) == false) {
				return false;
			}

			return $result[0]["private"] == 1;
		}

		public function count_topics($forum_id) {
			$query = "select count(*) as count from forum_topics where forum_id=%d";

			if (($result = $this->db->execute($query, $forum_id)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function count_messages($topic_id) {
			$query = "select count(*) as count from forum_messages where forum_topic_id=%d";

			if (($result = $this->db->execute($query, $topic_id)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_topic($topic_id, $offset, $limit) {
			if (($topic = $this->db->entry("forum_topics", $topic_id)) == false) {
				return false;
			}

			if (($forum = $this->get_forum_record($topic["forum_id"])) == false) {
				return false;
			}

			$topic["section"] = $forum["section"];
			$topic["title"] = $forum["title"];

			$query = "select *, UNIX_TIMESTAMP(timestamp) as timestamp, ".
			         "(select fullname from users where id=m.user_id) as author, ".
			         "(select avatar from users where id=m.user_id) as avatar, ".
			         "(select signature from users where id=m.user_id) as signature ".
			         "from forum_messages m where forum_topic_id=%d order by timestamp limit %d,%d";
			if (($messages = $this->db->execute($query, $topic_id, $offset, $limit)) === false) {
				return false;
			}

			foreach ($messages as $m => $message) {
				if ($message["username"] != "") {
					$messages[$m]["author"] = $message["username"];
				}
				unset($messages[$m]["username"]);
			}

			$topic["messages"] = $messages;

			if ($this->user->logged_in) {
				$this->mark_topic_read($topic_id);
			}

			return $topic;
		}

		public function mark_all_read() {
			if ($this->user->logged_in == false) {
				return;
			}

			$query = "delete from forum_last_view where user_id=%d";
			$this->db->query($query, $this->user->id);

			$data = array("user_id" => $this->user->id, "forum_topic_id" => null);
			$this->db->insert("forum_last_view", $data);
		}

		public function mark_forum_read($forum_id) {
			if ($this->user->logged_in == false) {
				return;
			}

			$query = "select id from forum_topics where forum_id=%d";
			if (($topics = $this->db->execute($query, $forum_id)) === false) {
				return;
			}

			foreach ($topics as $topic) {
				$this->mark_topic_read($topic["id"]);
			}
		}

		private function mark_topic_read($topic_id) {
			$query = "delete from forum_last_view where user_id=%d and forum_topic_id=%d";
			$this->db->query($query, $this->user->id, $topic_id);

			$data = array("user_id" => $this->user->id, "forum_topic_id" => $topic_id);
			$this->db->insert("forum_last_view", $data);
		}

		public function last_topic_view($topic_id) {
			$query = "select UNIX_TIMESTAMP(last_view) as last_view from forum_last_view ".
					 "where forum_topic_id=%d and user_id=%d";

			if (($lastview = $this->db->execute($query, $topic_id, $this->user->id)) != false) {
				return $lastview[0]["last_view"];
			}

			$query = "select UNIX_TIMESTAMP(last_view) as last_view from forum_last_view ".
					 "where forum_topic_id is null and user_id=%d";

			if (($lastview = $this->db->execute($query, $this->user->id)) != false) {
				return $lastview[0]["last_view"];
			}

			return 0;
		}

		public function topic_okay($topic) {
			if (($forum = $this->get_forum_record($topic["forum_id"])) == false) {
				$this->user->log_action("post attempt to restricted forum");
				return false;
			}

			$result = $this->response_okay($topic);

			if (trim($topic["subject"]) == "") {
				$this->view->add_message("Empty subject not allowed.");
				$result = false;
			}

			return $result;
		}

		public function response_okay($response) {
			$result = true;

			if (isset($response["forum_topic_id"])) {
				if (($topic = $this->db->entry("forum_topics", $response["forum_topic_id"])) == false) {
					return false;
				}

				if (($forum = $this->get_forum_record($topic["forum_id"])) == false) {
					$this->user->log_action("post attempt to restricted topic");
					return false;
				}
			}

			if ($this->user->logged_in == false) {
				if (trim($response["username"]) == "") {
					$this->view->add_message("Enter your name.");
					$result = false;
				} else {
					$name = preg_replace('/  */', " ", trim($response["username"]));
					$query = "select * from users where fullname=%s";
					if (($x = $this->db->execute($query, $name)) != false) {
						$this->view->add_message("That name is not allowed.");
						$result = false;
					}
				}
			}

			if (trim($response["content"]) == "") {
				$this->view->add_message("An empty message is not allowed.");
				$result = false;
			} else {
				$message = new \Banshee\message($response["content"]);

				if ($this->user->logged_in == false) {
					if ($message->is_spam) {
						$this->view->add_message("This message was seen as spam.");
						$result = false;
					}
				}

				if (($bbcodes = $message->bbcodes_left_open()) != null) {
					$this->view->add_message("These BB codes have not been opened or closed: %s", implode(", ", $bbcodes));
					$result = false;
				}
			}

			return $result;
		}

		public function create_topic($topic) {
			$queries = array();

			$query = "insert into forum_topics values(null, %d, %s, %d, %d)";
			array_push($queries, array($query, $topic["forum_id"], $topic["subject"], NO, NO));

			if ($this->user->logged_in) {
				$query = "insert into forum_messages values(null, {LAST_INSERT_ID}, %d, null, %s, %s, %s)";
				array_push($queries, array($query, $this->user->id, date("Y-m-d H:i:s"), $topic["content"], $_SERVER["REMOTE_ADDR"]));
			} else {
				$query = "insert into forum_messages values(null, {LAST_INSERT_ID}, null, %s, %s, %s, %s)";
				array_push($queries, array($query, $topic["username"], date("Y-m-d H:i:s"), $topic["content"], $_SERVER["REMOTE_ADDR"]));
			}

			if ($this->db->transaction($queries) === false) {
				return false;
			}

			$topic_id = $this->db->last_insert_id(2);

			if ($this->user->logged_in) {
				$this->mark_topic_read($topic_id);
			}

			return $topic_id;
		}

		public function create_response($response) {
			if (($topic = $this->db->entry("forum_topics", $response["forum_topic_id"])) == false) {
				return false;
			}
			if (is_true($topic["closed"])) {
				return false;
			}

			$keys = array("id", "forum_topic_id", "user_id", "username", "timestamp", "content", "ip_address");

			$response["id"] = null;
			if ($this->user->logged_in) {
				$response["user_id"] = $this->user->id;
				$response["username"] = null;
			} else {
				$response["user_id"] = null;
			}
			$response["timestamp"] = null;
			$response["ip_address"] = $_SERVER["REMOTE_ADDR"];

			if ($this->db->insert("forum_messages", $response, $keys) === false) {
				return false;
			}

			if ($this->user->logged_in) {
				$this->mark_topic_read($response["forum_topic_id"]);
			}

			return true;
		}

		public function get_message($message_id) {
			static $cache = array();

			if (isset($cache[$message_id]) == false) {
				if (($message = $this->db->entry("forum_messages", $message_id)) == false) {
					return false;
				}
				unset($message["ip_address"]);

				$cache[$message_id] = $message;
			}

			return $cache[$message_id];
		}

		public function may_edit($message) {
			if ($this->user->logged_in == false) {
				return false;
			}

			if (is_array($message) == false) {
				if (($message = $this->get_message($message)) == false) {
					return false;
				}
			}

			if (($topic = $this->db->entry("forum_topics", $message["forum_topic_id"])) == false) {
				return false;
			}
			if (is_true($topic["closed"])) {
				return false;
			}

			if ($this->user->is_admin || $this->user->has_role($this->settings->forum_maintainers)) {
				return true;
			}

			if (isset($message["user_id"]) == false) {
				return false;
			}

			return $message["user_id"] == $this->user->id;
		}

		public function update_message($data) {
			$content = array("content" => $data["message"]);

			if ($data["username"] != null) {
				if (($message = $this->get_message($data["message_id"])) != false) {
					if ($message["user_id"] == null) {
						$content["username"] = $data["username"];
					}
				}
			}

			return $this->db->update("forum_messages", $data["message_id"], $content) !== false;
		}

		public function delete_message($message_id) {
			$query = "select forum_topic_id from forum_messages where id=%d";
			if (($result = $this->db->execute($query, $message_id)) == false) {
				return false;
			}
			$topic_id = $result[0]["forum_topic_id"];

			if ($this->db->delete("forum_messages", $message_id) === false) {
				return false;
			}

			$query = "select count(*) as count from forum_messages where forum_topic_id=%d";
			if (($result = $this->db->execute($query, $topic_id)) == false) {
				return false;
			}

			if ($result[0]["count"] > 0) {
				return true;
			}

			return $this->borrow("cms/forum")->delete_topic($topic_id);
		}
	}
?>
