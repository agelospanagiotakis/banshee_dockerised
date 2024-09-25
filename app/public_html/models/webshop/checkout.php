<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class webshop_checkout_model extends Banshee\splitform_model {
		protected $forms = array(
			"address" => array("name", "address", "zipcode", "city", "country"),
			"payment" => array("creditcard"),
			"confirm" => array());

		public function get_articles($article_ids) {
			if (($articles = $this->borrow("webshop/cart")->get_articles($article_ids)) === false) {
				return false;
			}

			return $articles;
		}

		private function fields_filled($data) {
			$result = true;
			foreach ($this->forms[$this->current_form] as $element) {
				if (trim($data[$element]) == "") {
					$this->view->add_message("The ".$element." cannot be empty.");
					$result = false;
				}
			}

			return $result;
		}

		public function validate_address($data) {
			return $this->fields_filled($data);
		}

		public function validate_payment($data) {
			return $this->fields_filled($data);
		}

		public function process_form_data($data) {
			$article_ids = array_keys($_SESSION["webshop_cart"]);
			if (($data["articles"] = $this->get_articles($article_ids)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			foreach ($data["articles"] as $key => $article) {
				$a = $_SESSION["webshop_cart"][$article["id"]];
				$data["articles"][$key]["quantity"] = $a["quantity"];
				$data["articles"][$key]["price"] = $a["price"];
			}

			if (count($data["articles"]) == 0) {
				$this->view->add_tag("result", "Your shopping cart is empty!", array("url" => "webshop"));
			} else if ($this->place_order($data) == false) {
				$this->view->add_message("Error while placing order!");
				return false;
			} else {
				$this->send_notification($data);
			}

			$_SESSION["webshop_cart"] = array();

			return true;
		}

		public function place_order($order) {
			if ($this->db->query("begin") === false) {
				return false;
			}

			$data = array(
				"id" => null,
				"user_id"   => $this->user->id,
				"timestamp" => date("Y-m-d H:i:s"),
				"name"      => $order["name"],
				"address"   => $order["address"],
				"zipcode"   => $order["zipcode"],
				"city"      => $order["city"],
				"country"   => $order["country"],
				"closed"    => NO);
			if ($this->db->insert("shop_orders", $data) === false) {
				$this->db->query("rollback");
				return false;
			}
			$order_id = $this->db->last_insert_id;

			foreach ($order["articles"] as $article) {
				$data = array(
					"shop_article_id" => $article["id"],
					"shop_order_id"   => $order_id,
					"quantity"        => $article["quantity"],
					"article_price"   => $article["price"]);
				if ($this->db->insert("shop_order_article", $data) === false) {
					$this->db->query("rollback");
					return false;
				}
			}

			return $this->db->query("commit") !== false;
		}

		public function send_notification($data) {
			$notification = new \Banshee\Protocol\email("Order placed", $this->settings->webmaster_email, $this->settings->head_title." webshop");
			$notification->message("Your order has been placed successfully.");

			$notification->send($this->user->email, $this->user->fullname);
		}
	}
?>
