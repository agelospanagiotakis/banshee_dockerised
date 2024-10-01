<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_webshop_order_controller extends Banshee\controller {
		private function show_order_overview() {
			$closed = $_SESSION["admin_webshop_orders_closed"];

			if (($order_count = $this->model->count_orders($closed)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$pagination_id = "admin_webshop_order";
			if ($closed) {
				$pagination_id .= "_closed";
			}
			$pagination = new \Banshee\pagination($this->view, $pagination_id, $this->settings->admin_page_size, $order_count);

			if ($order_count == 0) {
				$orders = array();
			} else if (($orders = $this->model->get_orders($closed, $pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$this->view->open_tag("overview");

			$this->view->open_tag("orders", array("currency" => WEBSHOP_CURRENCY, "closed" => show_boolean($closed)));
			foreach ($orders as $order) {
				$order["timestamp"] = date("j F Y, H:i:s", $order["timestamp"]);
				$this->view->record($order, "order");
			}
			$this->view->close_tag();

			$pagination->show_browse_links();

			$this->view->close_tag();
		}

		private function show_order($order_id) {
			if (($order = $this->model->get_order($order_id)) == false) {
				$this->view->add_tag("result", "Order not found.");
				return;
			}

			$this->view->open_tag("edit");

			$order["timestamp"] = date("j F Y, H:i:s", $order["timestamp"]);
			$order["closed"] = show_boolean($order["closed"]);
			$this->view->record($order, "order");

			$total = 0;
			$count = 0;
			foreach ($order["articles"] as $article) {
				$total += $article["quantity"] * $article["price"];
				$count += $article["quantity"];
			}

			$this->view->open_tag("articles", array(
				"total"    => sprintf("%.2f", $total),
				"quantity" => $count,
				"currency" => WEBSHOP_CURRENCY));
			foreach ($order["articles"] as $article) {
				$this->view->record($article, "article");
			}
			$this->view->close_tag();

			$this->view->close_tag();
		}

		public function execute() {
			if (isset($_SESSION["admin_webshop_orders_closed"]) == false) {
				$_SESSION["admin_webshop_orders_closed"] = NO;
			}

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "type") {
					/* Change overview type
					 */
					$_SESSION["admin_webshop_orders_closed"] = is_true($_POST["type"]) ? YES : NO;
					$this->show_order_overview();
				} else if (valid_input($_POST["id"], VALIDATE_NUMBERS, VALIDATE_NONEMPTY) == false) {
					$this->show_order_overview();
				} else if ($_POST["submit_button"] == "Close order") {
					/* Close order
					 */
					if ($this->model->close_order($_POST["id"]) == false) {
						$this->view->add_message("Error while closing order.");
						$this->show_order($_POST["id"]);
					} else {
						$this->user->log_action("order %d closed", $_POST["id"]);
						$this->show_order_overview();
					}
				} else if ($_POST["submit_button"] == "Delete order") {
					/* Delete order
					 */
					if ($this->model->delete_order($_POST["id"]) == false) {
						$this->view->add_message("Error while deleting order.");
						$this->show_order($_POST["id"]);
					} else {
						$this->user->log_action("order %d deleted", $_POST["id"]);
						$this->show_order_overview();
					}
				} else {
					$this->show_order_overview();
				}
			} else if ($this->page->parameter_numeric(0)) {
				$this->show_order($this->page->parameters[0]);
			} else {
				$this->show_order_overview();
			}
		}
	}
?>
