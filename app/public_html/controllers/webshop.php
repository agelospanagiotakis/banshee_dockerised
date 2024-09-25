<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class webshop_controller extends Banshee\controller {
		protected $prevent_repost = true;

		public function show_articles($search, $category) {
			if (($article_count = $this->model->count_articles($search, $category)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$pagination = new \Banshee\pagination($this->view, "webshop_articles", $this->settings->webshop_page_size, $article_count);

			if ($article_count == 0) {
				$articles = array();
			} else if (($articles = $this->model->get_articles($pagination->offset, $pagination->size, $search, $category)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if (($art_count = count($articles)) == 0) {
					$this->view->add_tag("result", "No matching articles have been found.");
					$_SESSION["webshop_search"] = null;
					return;
				}

				$_SESSION["webshop_search_count"] = $art_count;
				if (($search != "") && ($_SESSION["webshop_search_count"] == 1)) {
					$this->show_article($articles[0]);
					return;
				}
			}

			if (($categories = $this->model->get_categories()) == false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$this->view->open_tag("webshop");

			$this->view->open_tag("categories", array("current" => (int)($_SESSION["webshop_category"] ?? 0)));
			foreach ($categories as $category) {
				$this->view->add_tag("category", $category["name"], array("id" => $category["id"]));
			}
			$this->view->close_tag();

			$this->view->open_tag("articles", array("currency" => WEBSHOP_CURRENCY));
			foreach ($articles as $article) {
				$article["price"] = sprintf("%.2f", $article["price"]);
				$this->view->record($article, "article");
			}
			$this->view->close_tag();

			$pagination->show_browse_links();

			$this->view->close_tag();
		}

		public function show_article($article) {
			$this->view->title = sprintf("%s - %s", $article["title"], $this->view->title);

			$this->view->add_javascript("banshee/notify.js");

			$article["price"] = sprintf("%.2f", $article["price"]);
			$this->view->record($article, "article", array("currency" => WEBSHOP_CURRENCY));
		}

		public function execute() {
			$this->view->title = "Webshop";

			$this->view->add_javascript("webshop.js");

			if (isset($_SESSION["webshop_cart"]) == false) {
				$_SESSION["webshop_cart"] = array();
			}

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "search") {
					$_SESSION["webshop_search"] = $_POST["search"];
				}
			} else if (($this->page->parameter_value(0) == false) && (($_SESSION["webshop_search_count"] ?? null) === 1)) {
				$_SESSION["webshop_search"] = null;
				$_SESSION["webshop_search_count"] = null;
			}

			if ($this->page->parameter_value(0, "category")) {
				if ($this->page->parameters[1] == 0) {
					$_SESSION["webshop_category"] = null;
				} else if (valid_input($this->page->parameters[1], VALIDATE_NUMBERS, VALIDATE_NONEMPTY)) {
					$_SESSION["webshop_category"] = $this->page->parameters[1];
				} else {
					$_SESSION["webshop_category"] = null;
				}
			}

			$cart_count = 0;
			foreach ($_SESSION["webshop_cart"] as $article) {
				$cart_count += $article["quantity"];
			}
			$this->view->add_tag("cart", $cart_count);

			$this->view->add_tag("search", $_SESSION["webshop_search"] ?? "");

			if ($this->page->parameter_numeric(0)) {
				if (($article = $this->model->get_article($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "Article not found.");
				} else {
					$this->show_article($article);
				}
			} else {
				$this->show_articles($_SESSION["webshop_search"] ?? null, $_SESSION["webshop_category"] ?? null);
			}
		}
	}
?>
