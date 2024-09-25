<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class search_model extends Banshee\model {
		private $text = null;
		private $add_or = null;

		public function __call($name, $args) {
			return false;
		}

		/* Add selection to query
		 */
		private function add_selection($column, &$query, &$args) {
			if ($this->add_or) {
				$query .= " or ";
			}
			$query .= "(".$column. " like %s)";
			array_push($args, "%".$this->text."%");

			$this->add_or = true;
		}

		/* Search agenda
		 */
		private function search_agenda() {
			$query = "select concat(%s, id) as url, title as text, content from agenda where (";
			$args = array("/agenda/");
			$this->add_selection("title", $query, $args);
			$this->add_selection("content", $query, $args);
			$query .= ") order by begin desc, end";

			return $this->db->execute($query, $args);
		}

		/* Search dictionary
		 */
		private function search_dictionary() {
			$query = "select concat(%s, id) as url, word as text, short_description as content ".
			         "from dictionary where ";
			$args = array("/dictionary/");
			$this->add_selection("word", $query, $args);
			$this->add_selection("long_description", $query, $args);

			return $this->db->execute($query, $args);
		}

		/* Search F.A.Q.
		 */
		private function search_faq() {
			$query = "select %s as url, question as text, answer as content from faqs where ";
			$args = array("/faq");
			$this->add_selection("question", $query, $args);
			$this->add_selection("answer", $query, $args);
			$query .= " order by question";

			return $this->db->execute($query, $args);
		}

		/* Search forum
		 */
		private function search_forum() {
			$query = "select distinct concat(%s, t.id, %s, m.id) as url, ".
			                         "concat(f.title, %s, t.subject) as text, m.content, m.timestamp ".
			         "from forums f, forum_topics t, forum_messages m ".
			         "where f.id=t.forum_id and t.id=m.forum_topic_id and (";
			$args = array("/forum/topic/", "/#", " :: ");
			$this->add_selection("t.subject", $query, $args);
			$this->add_selection("m.content", $query, $args);
			$this->add_selection("m.username", $query, $args);
			$query .= ") order by m.timestamp desc";

			return $this->db->execute($query, $args);
		}

		/* Search links
		 */
		private function search_links() {
			$query = "select link as url, text, concat(%s, link) as content from links where ";
			$args = array("Link to ");
			$this->add_selection("text", $query, $args);
			$this->add_selection("link", $query, $args);
			$query .= " order by text";

			return $this->db->execute($query, $args);
		}

		/* Search mailbox
		 */
		private function search_mailbox() {
			if ($this->user->logged_in == false) {
				return false;
			}

			$query = "select concat(%s, id) as url, subject as text, message as content from mailbox ".
			         "where to_user_id=%d and (status & %d = %d) and (";
			$args = array("/mailbox/", $this->user->id, MAIL_DELETED_BY_RECEIVER, 0);
			$this->add_selection("subject", $query, $args);
			$this->add_selection("message", $query, $args);
			$query .= ") order by timestamp desc";

			return $this->db->execute($query, $args);
		}

		/* Search news
		 */
		private function search_news() {
			$query = "select concat(%s, id) as url, title as text, content from news where (";
			$args = array("/news/");
			$this->add_selection("title", $query, $args);
			$this->add_selection("content", $query, $args);
			$query .= ") order by timestamp desc";

			return $this->db->execute($query, $args);
		}

		/* Search pages
		 */
		private function search_pages() {
			/* Public pages
			 */
			$args = array();
			$query = "select url, title as text, content from pages where ";
			if ($this->user->is_admin == false) {
				$query .= "private=%d and ";
				array_push($args, NO);
			}
			$query .= "(";
			$this->add_selection("title", $query, $args);
			$this->add_selection("content", $query, $args);
			$query .= ") and visible=%d";

			if (($public = $this->db->execute($query, $args, YES)) === false) {
				return false;
			}

			/* Search in private pages?
			 */
			if (($this->user->logged_in == false) || $this->user->is_admin) {
				return $public;
			}
			if (is_array($this->user->role_ids) == false) {
				return $public;
			}
			if (count($this->user->role_ids) == 0) {
				return $public;
			}

			$pages = implode(",", array_fill(0, count($this->user->role_ids), "%d"));

			/* Private pages
			 */
			$this->add_or = false;
			$args = array();
			$query = "select url, title as text, content from pages p, page_access a ".
			         "where p.id=a.page_id and p.private=%d and a.role_id in (".$pages.") and (";
			$this->add_selection("p.title", $query, $args);
			$this->add_selection("p.content", $query, $args);
			$query .= ") and p.visible=%d";

			if (($private = $this->db->execute($query, YES, $this->user->role_ids, $args, YES)) === false) {
				return false;
			}

			return array_merge($public, $private);
		}

		/* Search polls
		 */
		private function search_polls() {
			$now = date("Y-m-d H:i:s");
			$query = "select distinct concat(%s, p.id) as url, p.question as text, begin ".
			         "from polls p, poll_answers a ".
			         "where p.id=a.poll_id and %s>=end and (";
			$args = array("/poll/", $now);
			$this->add_selection("p.question", $query, $args);
			$this->add_selection("a.answer", $query, $args);
			$query .= ") order by begin desc";

			return $this->db->execute($query, $args);
		}

		/* Search photos
		 */
		private function search_photos() {
			/* Albums
			 */
			$query = "select concat(%s, id) as url, name as text, description as content ".
			         "from photo_albums where (";
			$args = array("/photo/");
			$this->add_selection("name", $query, $args);
			$this->add_selection("description", $query, $args);
			$query .= ") order by timestamp desc";
			if (($albums = $this->db->execute($query, $args)) === false) {
				return false;
			}

			/* Photos
			 */
			$this->add_or = false;
			$query = "select concat(%s, a.id) as url, p. title as text, a.description as content ".
			         "from photos p, photo_albums a where a.id=p.photo_album_id and ";
			$args = array("/photo/");
			$this->add_selection("title", $query, $args);
			$query .= " order by title";
			if (($photos = $this->db->execute($query, $args)) === false) {
				return false;
			}

			return array_merge($albums, $photos);
		}

		/* Search weblog
		 */
		private function search_weblog() {
			$query = "select distinct concat(%s, w.id) as url, w.title as text, w.content, w.timestamp ".
			         "from weblogs w left join weblog_comments c on w.id=c.weblog_id where (";
			$args = array("/weblog/");
			$this->add_selection("w.title", $query, $args);
			$this->add_selection("w.content", $query, $args);
			$this->add_selection("c.content", $query, $args);
			$this->add_selection("c.author", $query, $args);
			$query .= ") and w.visible=%d order by w.timestamp desc";

			return $this->db->execute($query, $args, YES);
		}

		/* Search webshop
		 */
		private function search_webshop() {
			$query = "select distinct concat(%s, id) as url, title as text, short_description as content ".
			         "from shop_articles where (";
			$args = array("/webshop/");
			$this->add_selection("title", $query, $args);
			$this->add_selection("short_description", $query, $args);
			$this->add_selection("long_description", $query, $args);
			$query .= ") order by title desc";
			return $this->db->execute($query, $args);
		}

		/* Search the database
		 */
		public function search($post, $sections) {
			$this->text = $post["query"];
			$result = array();

			foreach ($sections as $section => $label) {
				if (is_true($post[$section] ?? false)) {
					$this->add_or = false;
					$hits = call_user_func(array($this, "search_".$section));
					if ($hits != false) {
						$result[$section] = $hits;
					}
				}
			}

			return $result;
		}
	}
?>
