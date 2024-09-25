<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class news_controller extends Banshee\controller {
		public function execute() {
			$this->view->description = "News";
			$this->view->keywords = "news";
			$this->view->title = "News";
			$this->view->add_alternate($this->settings->head_title." news", "application/rss+xml", "/news.xml");

			if ($this->page->type == "xml") {
				/* RSS feed
				 */
				$rss = new \Banshee\Protocol\RSS($this->view);
				if ($rss->fetch_from_cache("news_rss") == false) {
					$rss->title = $this->settings->head_title." news";
					$rss->description = $this->settings->head_description;

					if (($news = $this->model->get_news(0, $this->settings->news_rss_page_size)) != false) {
						foreach ($news as $item) {
							$link = "/news/".$item["id"];
							$rss->add_item($item["title"], $item["content"], $link, $item["timestamp"]);
						}
					}
					$rss->add_to_view();
				}
			} else if ($this->page->parameter_numeric(0)) {
				/* News item
				 */
				if (($item = $this->model->get_news_item($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "Unknown news item");
				} else {
					$this->view->title = $item["title"]." - News";
					$item["timestamp"] = date_string("j F Y, H:i", strtotime($item["timestamp"]));
					$this->view->record($item, "news");
				}
			} else {
				/* News overview
				 */
				if (($count = $this->model->count_news()) === false) {
					$this->view->add_tag("result", "Database error");
					return;
				}

				$pagination = new \Banshee\pagination($this->view, "news", $this->settings->news_page_size, $count);

				if (($news = $this->model->get_news($pagination->offset, $pagination->size)) === false) {
					$this->view->add_tag("result", "Database error");
					return;
				}

				foreach ($news as $item) {
					$item["timestamp"] = date_string("j F Y, H:i", $item["timestamp"]);
					$this->view->record($item, "news");
				}

				$pagination->show_browse_links(7, 3);
			}
		}
	}
?>
