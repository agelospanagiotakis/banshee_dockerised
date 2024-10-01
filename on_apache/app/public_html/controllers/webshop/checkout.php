<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class webshop_checkout_controller extends Banshee\splitform_controller {
		protected $prevent_repost = true;
		protected $back_page = "webshop/cart";
		protected $button_submit = "Place order";

		protected function prepare_confirm() {
			foreach ($this->model->forms["address"] as $item) {
				$this->view->add_tag($item, $_SESSION["splitform"][$this->page->module]["values"][$item]);
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

		public function execute() {
			$this->view->title = "Checkout";

			if (count($_SESSION["webshop_cart"]) > 0) {
				$this->model->default_value("name", $this->user->fullname);
				$this->model->default_value("country", "The Netherlands");
				parent::execute();
			} else {
				$this->view->add_tag("result", "Your shopping cart is empty!", array("url" => "webshop"));
			}
		}
	}
?>
