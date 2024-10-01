<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_webshop_order_model extends Banshee\model {
		public function count_orders($closed) {
			$query = "select count(*) as count from shop_orders where closed=%d";

			if (($result = $this->db->execute($query, $closed ? YES : NO)) == false) {
				return false;
			}

			return (int)$result[0]["count"];
		}

		public function get_orders($closed, $offset, $limit) {
			$query = "select u.fullname, o.*, UNIX_TIMESTAMP(timestamp) as timestamp, ".
			         "(select sum(article_price * quantity) from shop_order_article where shop_order_id=o.id) as amount, ".
			         "(select sum(quantity) from shop_order_article where shop_order_id=o.id) as articles ".
			         "from shop_orders o, users u where o.user_id=u.id and closed=%d order by timestamp limit %d,%d";

			return $this->db->execute($query, $closed ? YES : NO, $offset, $limit);
		}

		public function get_order($order_id) {
			$query = "select o.*, u.email, UNIX_TIMESTAMP(timestamp) as timestamp ".
			         "from shop_orders o, users u where o.user_id=u.id and o.id=%d";

			if (($result = $this->db->execute($query, $order_id)) == false) {
				return false;
			}
			$order = $result[0];

			$query = "select a.id, a.article_nr, a.title, a.short_description, a.long_description, ".
			         "l.article_price as price, l.quantity ".
			         "from shop_articles a, shop_order_article l ".
			         "where a.id=l.shop_article_id and l.shop_order_id=%d order by title";
			if (($order["articles"] = $this->db->execute($query, $order["id"])) === false) {
				return false;
			}

			return $order;
		}

		public function close_order($order_id) {
			$data = array("closed" => YES);
			return $this->db->update("shop_orders", $order_id, $data) !== false;
		}

		public function delete_order($order_id) {
			$queries = array(
				array("delete from shop_order_article where shop_order_id=%d", $order_id),
				array("delete from shop_orders where id=%d", $order_id));

			return $this->db->transaction($queries);
		}
	}
?>
