<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_analytics_controller extends Banshee\controller {
		private $height = 100;
		private $page_width = 839;
		private $list_limit = 15;

		private function show_graph($items, $title) {
			static $id = -1;

			$total = 0;
			foreach ($items as $item) {
				$total += $item["count"];
			}
			if ($total == 0) {
				return;
			}

			$id = $id + 1;
			$max = $this->model->max_value($items, "count");

			$this->view->open_tag("graph", array("title" => $title, "id" => $id, "max" => $max));
			foreach ($items as $item) {
				if ($max > 0) {
					$item["height"] = round($this->height * ($item["count"] / $max));
				} else {
					$item["height"] = 0;
				}

				$timestamp = strtotime($item["date"]);
				$item["day"] = date("j F Y", $timestamp);
				$item["weekend"] = show_boolean(date("N", $timestamp) >= 6);

				$this->view->record($item, "item");
			}
			$this->view->close_tag();
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				$this->model->delete_referers($_POST);
			}

			$this->view->add_tag("width", floor($this->page_width / ANALYTICS_DAYS) - 1);
			$this->view->add_tag("height", $this->height);

			$this->view->add_javascript("cms/analytics.js");

			$day = valid_input($this->page->parameters[0] ?? null, VALIDATE_NUMBERS."-", VALIDATE_NONEMPTY) ? $this->page->parameters[0] : null;

			/* Visits
			 */
			if (($visits = $this->model->get_visits(ANALYTICS_DAYS)) === false) {
				return false;
			}
			$this->show_graph($visits, "Visits");

			/* Page views
			 */
			if (($pageviews = $this->model->get_page_views(ANALYTICS_DAYS)) === false) {
				return false;
			}
			$this->show_graph($pageviews, "Page views");

			/* Errors
			 */
			if (($errors = $this->model->get_errors(ANALYTICS_DAYS, 403)) === false) {
				return false;
			}
			$this->show_graph($errors, "403 Forbidden");

			if (($errors = $this->model->get_errors(ANALYTICS_DAYS, 404)) === false) {
				return false;
			}
			$this->show_graph($errors, "404 Not Found");

			if (($errors = $this->model->get_errors(ANALYTICS_DAYS, 500)) === false) {
				return false;
			}
			$this->show_graph($errors, "500 Internal Error");

			/* Hack attempts
			 */
			if (($errors = $this->model->get_errors(ANALYTICS_DAYS, EVENT_FAILED_LOGIN)) === false) {
				return false;
			}
			$this->show_graph($errors, "Failed logins");

			if (($errors = $this->model->get_errors(ANALYTICS_DAYS, EVENT_EXPLOIT_ATTEMPT)) === false) {
				return false;
			}
			$this->show_graph($errors, "Exploit attempts");

			/* Day deselect
			 */
			if ($day !== null) {
				$this->view->add_tag("deselect", date("j F Y", strtotime($day)), array("date" => $day));
			}

			/* Top pages
			 */
			if (($pages = $this->model->get_top_pages($this->list_limit, $day)) === false) {
				return false;
			}

			$this->view->open_tag("pages");
			foreach ($pages as $page) {
				$this->view->record($page, "page");
			}
			$this->view->close_tag();

			/* Referers
			 */
			$date = date("Y-m-d", strtotime("-7 days"));
			if (($referers = $this->model->get_referers($day)) === false) {
				return false;
			}

			$this->view->open_tag("referers");
			$hostname = null;
			foreach ($referers as $hostname => $host) {
				$total = 0;
				foreach ($host as $referer) {
					$total += $referer["count"];
				}
				$params = array(
					"hostname" => $hostname,
					"count"    => count($host),
					"total"    => $total);
				$this->view->open_tag("host", $params);
				foreach ($host as $referer) {
					$this->view->record($referer, "referer");
				}
				$this->view->close_tag();
			}
			$this->view->close_tag();
		}
	}
?>
