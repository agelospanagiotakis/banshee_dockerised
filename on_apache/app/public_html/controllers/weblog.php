<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class weblog_controller extends Banshee\controller {
		private $url = null;

		private function include_magnific_popup() {
			$this->view->add_javascript("banshee/jquery.magnific-popup.js");
			$this->view->add_javascript("weblog.js");

			$this->view->add_css("banshee/magnific-popup.css");
		}

		private function show_last_weblogs() {
			if (($weblogs = $this->model->get_last_weblogs($this->settings->weblog_page_size)) === false) {
				$this->view->add_tag("result", "Database error.", $this->url);
				return;
			}

			$this->include_magnific_popup();

			$this->view->open_tag("weblogs");

			foreach ($weblogs as $weblog) {
				$this->view->open_tag("weblog", array("id" => $weblog["id"]));

				$weblog["timestamp"] = date_string("j F Y, H:i", $weblog["timestamp"]);
				$weblog["title_link"] = urlencode($weblog["title"]);
				$this->view->record($weblog);

				/* Tags
				 */
				$this->view->open_tag("tags");
				foreach ($weblog["tags"] as $tag) {
					$this->view->add_tag("tag", $tag["tag"], array("id" => $tag["id"]));
				}
				$this->view->close_tag();

				$this->view->close_tag();
			}

			$this->view->close_tag();
		}

		private function show_weblog($weblog_id) {
			if (($weblog = $this->model->get_weblog($weblog_id)) === false) {
				$this->view->add_tag("result", "Weblog not found.", $this->url);
				return;
			}

			$this->include_magnific_popup();

			$this->view->title = $weblog["title"]." - Weblog";

			$weblog["timestamp"] = date_string("j F Y, H:i", $weblog["timestamp"]);

			$this->view->open_tag("weblog", array("id" => $weblog["id"]));

			$this->view->add_tag("title", $weblog["title"]);
			$this->view->add_tag("title_link", urlencode($weblog["title"]));
			$this->view->add_tag("content", $weblog["content"]);
			$this->view->add_tag("author", $weblog["author"]);
			$this->view->add_tag("timestamp", $weblog["timestamp"]);

			/* Tags
			 */
			$this->view->open_tag("tags");
			foreach ($weblog["tags"] as $tag) {
				$this->view->add_tag("tag", $tag["tag"], array("id" => $tag["id"]));
				$this->view->keywords .= ", ".$tag["tag"];
			}
			$this->view->close_tag();

			/* Comments
			 */
			$this->view->open_tag("comments");
			foreach ($weblog["comments"] as $comment) {
				unset($comment["weblog_id"]);
				unset($comment["ip_address"]);
				$message = new \Banshee\message($comment["content"]);
				$message->unescaped_output();
				$message->translate_bbcodes();
				$message->translate_smilies();
				$comment["content"] = $message->content;
				unset($message);

				$comment["timestamp"] = date_string("j F Y, H:i", $comment["timestamp"]);
				$this->view->record($comment, "comment");
			}
			$this->view->close_tag();

			$this->view->close_tag();
		}

		private function show_comment($comment) {
			$this->view->open_tag("comment");
			$this->view->add_tag("author", $comment["author"]);
			$this->view->add_tag("content", $comment["content"] ?? "");
			$this->view->close_tag();
		}

		public function execute() {
			$months_of_year = config_array(MONTHS_OF_YEAR);

			$this->view->title = "Weblog";
			$this->view->description = "Weblog";
			$this->view->keywords = "weblog";
			$this->view->add_alternate($this->settings->head_title." weblog", "application/rss+xml", "/weblog.xml");

			$this->url = array("url" => $this->page->page);

			/* Sidebar
			 */
			$this->view->open_tag("sidebar");

			/* Tags
			 */
			if (($tags = $this->model->get_all_tags()) != false) {
				$this->view->open_tag("tags");
				foreach ($tags as $tag) {
					$this->view->add_tag("tag", $tag["tag"], array("id" => $tag["id"]));
				}
				$this->view->close_tag();
			}

			/* Years
			 */
			if (($years = $this->model->get_years()) != false) {
				$this->view->open_tag("years");
				foreach ($years as $year) {
					$this->view->add_tag("year", $year["year"]);
				}
				$this->view->close_tag();
			}

			/* Periods
			 */
			if (($periods = $this->model->get_periods()) != false) {
				$this->view->open_tag("periods");
				foreach ($periods as $period) {
					$link = array("link" => $period["year"]."/".$period["month"]);
					$text = $months_of_year[$period["month"] - 1]." ".$period["year"];
					$this->view->add_tag("period", $text, $link);
				}
				$this->view->close_tag();
			}

			$this->view->close_tag();

			if ($this->page->type == "xml") {
				/* RSS feed
				 */
				$rss = new \Banshee\Protocol\RSS($this->view);
				if ($rss->fetch_from_cache("weblog_rss") == false) {
					$rss->title = $this->settings->head_title." weblog";
					$rss->description = $this->settings->head_description;

					if (($weblogs = $this->model->get_last_weblogs($this->settings->weblog_rss_page_size)) != false) {
						foreach ($weblogs as $weblog) {
							$link = "/weblog/".$weblog["id"];
							$rss->add_item($weblog["title"], $weblog["content"], $link, $weblog["timestamp"]);
						}
					}
					$rss->add_to_view();
				}
			} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
				/* Comment submits
				 */
				if ($this->model->comment_okay($_POST) == false) {
					$this->show_weblog($_POST["weblog_id"]);
					$this->show_comment($_POST);
				} else if ($this->model->add_comment($_POST) == false) {
					$this->view->add_message("Error while adding comment.");
					$this->show_weblog($_POST["weblog_id"]);
					$this->show_comment($_POST);
				} else {
					$this->show_weblog($_POST["weblog_id"]);
				}
			} else if ($this->page->parameter_value(0, "tag") && $this->page->parameter_numeric(1)) {
				/* Tagged weblogs
				 */
				if (($tag = $this->model->get_tag($this->page->parameters[1])) == false) {
					$this->view->add_tag("result", "Unknown tag", $this->url);
				} else if (($weblogs = $this->model->get_tagged_weblogs($this->page->parameters[1])) === false) {
					$this->view->add_tag("result", "Error fetching tags", $this->url);
				} else {
					$this->view->title = "Tag ".$tag." - Weblog";

					$this->view->open_tag("list", array("label" => "Weblogs with '".$tag."' tag"));
					foreach ($weblogs as $weblog) {
						$weblog["timestamp"] = date_string("j F Y", $weblog["timestamp"]);
						$this->view->record($weblog, "weblog");
					}
					$this->view->close_tag();
				}
			} else if ($this->page->parameter_value(0, "period") && $this->page->parameter_numeric(1) && valid_input($this->page->parameters[2] ?? "", VALIDATE_NUMBERS)) {
				/* Weblogs of certain period
				 */
				if (($weblogs = $this->model->get_weblogs_of_period($this->page->parameters[1], $this->page->parameters[2] ?? null)) === false) {
					$this->view->add_tag("result", "Error fetching weblogs", $this->url);
				} else {
					if (isset($this->page->parameters[2]) == false) {
						$this->view->title = "Year ".$this->page->parameters[1]." - Weblog";
					} else {
						$month = $months_of_year[($this->page->parameters[2] ?? 1) - 1] ?? "";
						$this->view->title = $month." ".$this->page->parameters[1]." - Weblog";
					}

					$month = 0;
					$count = count($weblogs);
					for ($i = 0; $i < $count; $i++) {
						if ((int)$weblogs[$i]["month"] != $month) {
							if ($month != 0) {
								$this->view->close_tag();
							}
							if ($i < $count) {
								$label = $months_of_year[$weblogs[$i]["month"] - 1]." ".$this->page->parameters[1];
								$this->view->open_tag("list", array("label" => $label));
							}
						}
						$weblogs[$i]["timestamp"] = date_string("j F Y", $weblogs[$i]["timestamp"]);
						$this->view->record($weblogs[$i], "weblog");
						$month = (int)$weblogs[$i]["month"];
					}
					if ($month != 0) {
						$this->view->close_tag();
					}
				}
			} else if ($this->page->parameter_value(0, "user") && $this->page->parameter_numeric(1)) {
				/* User weblogs
				 */
				if (($user = $this->model->get_user($this->page->parameters[1])) == false) {
					$this->view->add_tag("result", "Unknown user");
				} else if (($weblogs = $this->model->get_weblogs_by_user($this->page->parameters[1])) === false) {
					$this->view->add_tag("result", "Error fetching weblogs", $this->url);
				} else {
					$this->view->open_tag("list", array("label" => "Weblogs by ".$user["fullname"]));
					foreach ($weblogs as $weblog) {
						$weblog["timestamp"] = date_string("j F Y", $weblog["timestamp"]);
						$this->view->record($weblog, "weblog");
					}
					$this->view->close_tag();
				}
			} else if ($this->page->parameter_numeric(0)) {
				/* Show weblog
				 */
				$this->show_weblog($this->page->parameters[0]);
				if ($this->user->logged_in) {
					$this->show_comment(array("author" => $this->user->fullname));
				}
			} else {
				/* Show last weblogs
				 */
				$this->show_last_weblogs();
			}
		}
	}
?>
