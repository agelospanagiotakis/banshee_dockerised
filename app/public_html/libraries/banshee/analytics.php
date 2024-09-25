<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	class analytics {
		private $db = null;
		private $page = null;
		private $today = null;
		private $normal_user = true;
		private $required_headers = array("USER_AGENT", "ACCEPT_ENCODING", "ACCEPT_LANGUAGE");
		private $search_bots = array("bot", "spider", "crawl", "feed", "rss", "slurp",
			"thumbshots", "sogou", "claws", "wotbox", "blogtrottr", "feedbin");
		private $referer_spam = array("viagra", "pharma", "cheap", "sex", "gay", "lesbian", "porn",
			"latina", "asian", "girls", "women");

		/* Constructor
		 *
		 * INPUT:  object database, object page
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($db, $page) {
			$this->db = $db;
			$this->page = $page;
		}

		/* Magic method get
		 *
		 * INPUT:  string key
		 * OUTPUT: mixed value
		 * ERROR:  null
		 */
		public function __get($key) {
			switch ($key) {
				case "normal_user": return $this->normal_user;
			}

			return null;
		}

		/* Log visit
		 *
		 * INPUT:  -
		 * OUTPUT: -
		 * ERROR:  -
		 */
		private function log_visit() {
			$this->log_error(0);
		}

		/* Log error
		 *
		 * INPUT:  int error code
		 * OUTPUT: -
		 * ERROR:  -
		 */
		private function log_error($error) {
			$query = "update log_visits set count=count+1 where date=%s and error=%d";
			if (($result = $this->db->execute($query, $this->today, $error)) === false) {
				return;
			} else if ($result > 0) {
				return;
			}

			$data = array(
				"id"    => null,
				"date"  => $this->today,
				"count" => 1,
				"error" => $error);
			$this->db->insert("log_visits", $data);
		}

		/* Log referer
		 *
		 * INPUT:  string referer
		 * OUTPUT: -
		 * ERROR:  -
		 */
		private function log_referer($referer) {
			$parts = explode("/", $referer, 4);
			if (count($parts) < 4) {
				return;
			}
			list($hostname) = explode(":", $parts[2]);

			$dont_log = array(WEBSITE_DOMAIN, $_SERVER["HTTP_HOST"], $_SERVER["SERVER_NAME"], "localhost", "127.0.0.1", "");
			if (in_array($hostname, $dont_log)) {
				return;
			}

			$lans = array("192.168.", "10.", "172.16.");
			foreach ($lans as $lan) {
				if (substr($hostname, 0, strlen($lan)) == $lan) {
					return;
				}
			}

			foreach ($this->referer_spam as $spam) {
				if (strpos($hostname, $spam) !== false) {
					$this->normal_user = false;
					return;
				}
			}

			list($referer) = explode("#", $referer, 2);

			$query = "update log_referers set count=count+1 where hostname=%s and url=%s and date=%s";
			if (($result = $this->db->execute($query, $hostname, $referer, $this->today)) === false) {
				return;
			} else if ($result > 0) {
				return;
			}

			$data = array(
				"id"       => null,
				"hostname" => $hostname,
				"url"      => $referer,
				"date"     => $this->today,
				"count"    => 1);
			$this->db->insert("log_referers", $data);
		}

		/* Log page view
		 *
		 * INPUT:  string page
		 * OUTPUT: -
		 * ERROR:  -
		 */
		private function log_page_view($page) {
			$query = "update log_page_views set count=count+1 where page=%s and date=%s";
			if (($result = $this->db->execute($query, $page, $this->today)) === false) {
				return;
			} else if ($result > 0) {
				return;
			}

			$data = array(
				"id"    => null,
				"page"  => $page,
				"date"  => $this->today,
				"count" => 1);
			$this->db->insert("log_page_views", $data);
		}

		/* Execute logging
		 *
		 * INPUT:  -
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function execute() {
			if ($this->page->ajax_request) {
				return;
			}

			/* Don't log request that miss certain headers
			 */
			foreach ($this->required_headers as $header) {
				if (isset($_SERVER["HTTP_".$header]) == false) {
					return;
				}
			}

			/* Don't log hits from search bots
			 */
			foreach ($this->search_bots as $bot) {
				if (strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), $bot) !== false) {
					$this->normal_user = false;
					return;
				}
			}

			/* Don't log visits of admin and system pages
			 */
			$skip_pages = array("cms");
			if (in_array($this->page->page, $skip_pages)) {
				return;
			} else if (substr($this->page->page, 0, 4) == "cms/") {
				return;
			}

			$this->today = date("Y-m-d");

			/* Log visit and client
			 */
			if (isset($_SESSION["last_visit"]) == false) {
				$this->log_visit();

				if (isset($_SERVER["HTTP_ORIGIN"])) {
					$this->log_referer($_SERVER["HTTP_ORIGIN"]);
				} else if (isset($_SERVER["HTTP_REFERER"])) {
					$this->log_referer($_SERVER["HTTP_REFERER"]);
				}
			}

			/* Log error
			 */
			$log_errors = array(403, 404, 500);
			if (in_array($this->page->http_code, $log_errors)) {
				$this->log_error($this->page->http_code);
			}

			/* Log page view
			 */
			$this->log_page_view($this->page->page);
		}
	}
?>
