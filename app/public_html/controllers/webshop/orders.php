<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class webshop_orders_controller extends Banshee\controller {
		protected $prevent_repost = true;

		public function execute() {
			$this->view->title = "Orders";

			if (isset($_SESSION["webshop_orders_closed"]) == false) {
				$_SESSION["webshop_orders_closed"] = NO;
			}

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				$_SESSION["webshop_orders_closed"] = is_true($_POST["type"]) ? YES : NO;
			}

			$closed = $_SESSION["webshop_orders_closed"];

			if (($order_count = $this->model->count_orders($closed)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$pagination_id = "webshop_order";
			if ($closed) {
				$pagination_id .= "_closed";
			}
			$pagination = new \Banshee\pagination($this->view, $pagination_id, $this->settings->webshop_order_page_size, $order_count);

			if (($orders = $this->model->get_orders($closed, $pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$this->view->open_tag("orders", array("closed" => show_boolean($closed)));

			foreach ($orders as $order) {
				$this->view->open_tag("order");

				$order["timestamp"] = date_string("j F Y, H:i:s", $order["timestamp"]);
				$this->view->record($order);

				$total = 0;
				$count = 0;
				foreach ($order["articles"] as $article) {
					$total += $article["quantity"] * $article["price"];
					$count += $article["quantity"];
				}

				$this->view->open_tag("articles", array("total" => $total, "count" => $count, "currency" => WEBSHOP_CURRENCY));
				foreach ($order["articles"] as $article) {
					$this->view->record($article, "article");
				}
				$this->view->close_tag();

				$this->view->close_tag();
			}

			$pagination->show_browse_links();

			$this->view->close_tag();
		}
	}
?>
