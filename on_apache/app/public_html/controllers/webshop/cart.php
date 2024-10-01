<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class webshop_cart_controller extends Banshee\controller {
		protected $prevent_repost = true;

		private function add($article_id) {
			if (($article = $this->model->get_article($article_id)) == false) {
				$this->view->add_tag("result", "Article not found.");
				return;
			}

			if (is_array($_SESSION["webshop_cart"][$article_id]) == false) {
				$_SESSION["webshop_cart"][$article_id] = array(
					"quantity" => 1,
					"price"    => $article["price"]);
			} else {
				$_SESSION["webshop_cart"][$article_id]["quantity"]++;
			}

			$this->view->add_tag("result", "ok");

			$quantity = 0;
			foreach ($_SESSION["webshop_cart"] as $article) {
				$quantity += $article["quantity"];
			}
			$this->view->add_tag("quantity", $quantity);
		}

		public function execute() {
			$this->view->title = "Shopping cart";

			if (isset($_SESSION["webshop_cart"]) == false) {
				$_SESSION["webshop_cart"] = array();
			}

			if ($this->page->ajax_request) {
				if ($this->page->parameters[0] == "add") {
					$this->add($this->page->parameters[1]);
				}
				return;
			}

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if (is_array($_SESSION["webshop_cart"][$_POST["id"]]) == false) {
					$this->view->add_system_message("That article is no longer present in the shopping cart.");
				} else {
					if ($_POST["submit_button"] == "+") {
						$_SESSION["webshop_cart"][$_POST["id"]]["quantity"]++;
					} else if ($_POST["submit_button"] == "-") {
						if (--$_SESSION["webshop_cart"][$_POST["id"]]["quantity"] == 0) {
							unset($_SESSION["webshop_cart"][$_POST["id"]]);
						}
					}
				}
			}

			$article_ids = array_keys($_SESSION["webshop_cart"]);
			if (($articles = $this->model->get_articles($article_ids)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$total = 0;
			$count = 0;
			foreach ($articles as $article) {
				$total += $article["quantity"] * $article["price"];
				$count += $article["quantity"];
			}

			$this->view->open_tag("cart", array(
				"currency" => WEBSHOP_CURRENCY,
				"total"    => sprintf("%.2f", $total),
				"quantity" => $count));

			foreach ($articles as $article) {
				$this->view->record($article, "article");
			}

			$this->view->close_tag();
		}
	}
?>
