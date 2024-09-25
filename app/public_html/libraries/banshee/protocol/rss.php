<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee\Protocol;

	class RSS {
		const CONTENT_TYPE = "application/rss+xml; charset=utf-8";

		private $view = null;
		private $protocol = null;
		private $cache_id = null;
		private $title = null;
		private $description = null;
		private $url = null;
		private $items = array();

		/* Constructor
		 *
		 * INPUT:  object view
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($view) {
			$this->view = $view;

			if (isset($_SERVER["HTTP_SCHEME"])) {
				$this->protocol = $_SERVER["HTTP_SCHEME"];
			} else if ($_SERVER["HTTPS"] == "on") {
				$this->protocol = "https";
			} else {
				$this->protocol = "http";
			}

			$this->url = sprintf("%s://%s/", $this->protocol, $_SERVER["SERVER_NAME"]);
		}

		/* Magic method set
		 *
		 * INPUT:  string key, mixed value
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __set($key, $value) {
			switch ($key) {
				case "title": $this->title = $value; break;
				case "description": $this->description = $value; break;
				case "url": $this->url = $value; break;
			}
		}

		/* Fetch RSS data from cache
		 *
		 * INPUT:  string cache id
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function fetch_from_cache($cache_id) {
			$this->view->content_type = self::CONTENT_TYPE;
			$this->cache_id = $cache_id;

			return $this->view->fetch_from_cache($cache_id);
		}

		/* Add RSS item
		 *
		 * INPUT:  string title, string description, string link, int timestamp
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function add_item($title, $description, $link, $timestamp) {
			if ($link[0] == "/") {
				$link = sprintf("%s://%s%s", $this->protocol, $_SERVER["SERVER_NAME"], $link);
			}

			array_push($this->items, array(
				"title"       => $title,
				"description" => $description,
				"link"        => $link,
				"timestamp"   => date("r", $timestamp)));
		}

		/* Send RSS feed to client
		 *
		 * INPUT:  -
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function add_to_view() {
			$this->view->content_type = self::CONTENT_TYPE;

			if ($this->cache_id !== null) {
				$this->view->start_caching($this->cache_id);
			}

			$this->view->open_tag("rss_feed");

			if ($this->title !== null) {
				$this->view->add_tag("title", $this->title);
			}
			if ($this->description !== null) {
				$this->view->add_tag("description", $this->description);
			}
			if ($this->url !== null) {
				$this->view->add_tag("url", $this->url);
			}

			$this->view->open_tag("items");
			foreach ($this->items as $item) {
				$this->view->record($item, "item");
			}
			$this->view->close_tag();

			$this->view->close_tag();

			if ($this->cache_id !== null) {
				$this->view->stop_caching();
			}
		}
	}
?>
