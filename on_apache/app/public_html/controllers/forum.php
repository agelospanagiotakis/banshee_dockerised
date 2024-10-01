<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class forum_controller extends Banshee\controller {
		private $url = null;

		private function show_forum_overview() {
			$this->view->add_javascript("forum.js");

			if (($forums = $this->model->get_forums()) === false) {
				$this->view->add_tag("result", "Database error.", $this->url);
			} else {
				$private = show_boolean($this->user->logged_in);
				$this->view->open_tag("forums", array("private" => $private));
				foreach ($forums as $forum) {
					$this->view->record($forum, "forum", array(), true);
				}
				$this->view->close_tag();
			}
		}

		private function show_topic_form($topic) {
			$this->view->add_javascript("forum.js");
			$this->view->add_help_button();

			$this->view->record($topic, "newtopic");
			$this->show_smilies();
		}

		private function show_forum($forum_id) {
			if (($count = $this->model->count_topics($forum_id)) === false) {
				$this->view->add_tag("result", "Database error while counting topics.");
				return;
			}

			$pagination = new \Banshee\pagination($this->view, "forum_".$forum_id, $this->settings->forum_page_size, $count);

			if (($forum = $this->model->get_forum($forum_id, $pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Forum not found.", $this->url);
				return;
			}

			$this->view->add_javascript("forum.js");

			$this->view->title = $forum["title"]." - Forum";

			$this->view->open_tag("forum", array("id" => $forum["id"]));
			$this->view->add_tag("section", $forum["section"]);
			$this->view->add_tag("title", $forum["title"]);

			$this->view->open_tag("topics");
			foreach ($forum["topics"] as $topic) {
				if ($this->user->logged_in) {
					$topic["unread"] = show_boolean($topic["last_view"] < $topic["timestamp"]);
				}
				$topic["timestamp"] = date_string("j F Y, H:i", $topic["timestamp"]);
				$topic["sticky"] = show_boolean($topic["sticky"]);
				$topic["closed"] = show_boolean($topic["closed"]);
				$this->view->record($topic, "topic");
			}
			$this->view->close_tag();

			$pagination->show_browse_links();

			$this->view->close_tag();
		}

		private function show_smilies() {
			$smilies = config_file("smilies");

			$this->view->open_tag("smilies");
			foreach ($smilies as $smiley) {
				$smiley = explode("\t", chop($smiley));
				$text = array_shift($smiley);
				$image = array_pop($smiley);

				$this->view->add_tag("smiley", $image, array("text" => $text));
			}
			$this->view->close_tag();
		}

		private function message_to_html($message) {
			$post = new \Banshee\message($message);
			$post->unescaped_output();
			$post->translate_bbcodes();
			$post->translate_smilies();

			return $post->content;
		}

		private function show_topic($topic_id, $response = null) {
			$moderator = $this->user->is_admin || $this->user->has_role($this->settings->forum_maintainers);

			if ($this->user->logged_in) {
				$last_view = $this->model->last_topic_view($topic_id);
			}

			if (($count = $this->model->count_messages($topic_id)) === false) {
				$this->view->add_tag("result", "Database error while counting topics.");
				return;
			}

			$this->view->add_help_button();

			$pagination = new \Banshee\pagination($this->view, "forum_topic_".$topic_id, $this->settings->forum_page_size, $count, true);

			if (($topic = $this->model->get_topic($topic_id, $pagination->offset, $pagination->size)) == false) {
				$this->view->add_tag("result", "Topic not found.", $this->url);
			} else {
				$this->view->add_javascript("forum.js");

				$this->view->title = $topic["subject"]." - Forum";
				$this->view->open_tag("topic", array(
					"id"        => $topic["id"],
					"forum_id"  => $topic["forum_id"],
					"moderator" => show_boolean($moderator),
					"closed"    => show_boolean($topic["closed"])));

				$this->view->add_tag("section", $topic["section"]);
				$this->view->add_tag("title", $topic["title"]);
				$this->view->add_tag("subject", $topic["subject"]);

				foreach ($topic["messages"] as $message) {
					if ($this->user->logged_in) {
						$message["unread"] = show_boolean($last_view < $message["timestamp"]);
					}
					$message["usertype"] = ($message["user_id"] == "") ? "unregistered" : "registered";
					$message["timestamp"] = date_string("j F Y, H:i", $message["timestamp"]);
					$message["content"] = preg_replace("/\[(config|code|quote)\]([\r\n]*)/", "[$1]", $message["content"]);

					if ($message["avatar"] == "") {
						$message["avatar"] = EMPTY_AVATAR;
					}

					$message["content"] = $this->message_to_html($message["content"]);
					unset($post);

					if ($message["signature"] != "") {
						$post = new \Banshee\message($message["signature"]);
						$post->unescaped_output();
						$post->translate_bbcodes();
						$message["signature"] = $post->content;
						unset($post);
					}

					$message["edit"] = show_boolean($this->model->may_edit($message));

					$this->view->record($message, "message");
				}

				if ($response != null) {
					$this->view->record($response, "response");
				}

				$this->view->close_tag();

				$this->show_smilies();

				$pagination->show_browse_links();
			}
		}

		public function execute() {
			$this->view->description = "Banshee forum";
			$this->view->keywords = "forum";
			$this->view->title = "Forum";

			$this->url = array("url" => $this->page->page);

			if ($this->page->ajax_request) {
				if ($_SERVER["REQUEST_METHOD"] == "POST") {
					if ($_POST["submit_button"] == "Save") {
						if ($this->model->may_edit($_POST["message_id"])) {
							$this->model->update_message($_POST);

							$this->view->add_tag("message", $this->message_to_html($_POST["message"]));
						} else {
							$this->page->set_http_code(403);
						}
					} else if ($_POST["submit_button"] == "Delete") {
						if ($this->model->may_edit($_POST["message_id"])) {
							$this->model->delete_message($_POST["message_id"]);

							$this->view->add_tag("message", $this->message_to_html($_POST["message"]));
						} else {
							$this->page->set_http_code(403);
						}
					} else if ($_POST["submit_button"] == "Preview response") {
						$this->view->add_tag("message", $this->message_to_html($_POST["message"]));
					}
				} else if ($this->page->parameters[0] == "message") {
					if (($message = $this->model->get_message($this->page->parameters[1])) != false) {
						$this->view->record($message, "message");
					}
				}
				return;
			}

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Create topic") {
					/* Create new topic
					 */
					if ($this->model->topic_okay($_POST) == false) {
						$this->show_topic_form($_POST);
					} else if (($topic_id = $this->model->create_topic($_POST)) == false) {
						$this->view->add_message("Database error while creating topic.");
						$this->show_topic_form($_POST);
					} else {
						$this->show_topic($topic_id);
					}
				} else if ($_POST["submit_button"] == "Post response") {
					/* Respond to topic
					 */
					if ($this->model->response_okay($_POST) == false) {
						$this->show_topic($_POST["forum_topic_id"], $_POST);
					} else if ($this->model->create_response($_POST) == false) {
						$this->view->add_message("Database error while saving response.");
						$this->show_topic($_POST["forum_topic_id"], $_POST);
					} else {
						$this->show_topic($_POST["forum_topic_id"]);
					}
				} else if ($_POST["submit_button"] == "Mark forum as read") {
					/* Mark all topics in forum as read
					 */
					$this->model->mark_forum_read($_POST["forum_id"]);
					$this->show_forum($_POST["forum_id"]);
				} else {
					$this->show_forum_overview();
				}
			} else if ($this->page->parameter_value(0, "topic") && $this->page->parameter_numeric(1)) {
				/* Show topic
				 */
				$this->show_topic($this->page->parameters[1]);
			} else if ($this->page->parameter_value(0)) {
				if ($this->page->parameter_value(1, "new")) {
					/* Start new topic
					 */
					$topic = array("forum_id" => $this->page->parameters[0]);
					$this->show_topic_form($topic);
				} else {
					/* Show forum
					 */
					$this->show_forum($this->page->parameters[0]);
				}
			} else {
				/* Show forums
				 */
				$this->show_forum_overview();
			}
		}
	}
?>
