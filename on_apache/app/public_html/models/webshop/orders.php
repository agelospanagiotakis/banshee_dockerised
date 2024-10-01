<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class webshop_orders_model extends Banshee\model {
		public function count_orders($closed) {
			$query = "select count(*) as count from shop_orders where user_id=%d and closed=%d";

			if (($result = $this->db->execute($query, $this->user->id, $closed)) == false) {
				return false;
			}

			return (int)$result[0]["count"];
		}

		public function get_orders($closed, $offset, $limit) {
			$query = "select *, UNIX_TIMESTAMP(timestamp) as timestamp ".
			         "from shop_orders where user_id=%d and closed=%d ".
			         "order by timestamp limit %d,%d";

			if (($orders = $this->db->execute($query, $this->user->id, $closed, $offset, $limit)) === false) {
				return false;
			}

			$query = "select a.id, a.article_nr, a.title, a.short_description, a.long_description, ".
			         "l.article_price as price, l.quantity ".
			         "from shop_articles a, shop_order_article l ".
			         "where a.id=l.shop_article_id and l.shop_order_id=%d order by title";
			foreach ($orders as $i => $order) {
				if (($orders[$i]["articles"] = $this->db->execute($query, $order["id"])) === false) {
					return false;
				}
			}

			return $orders;
		}
	}
?>
