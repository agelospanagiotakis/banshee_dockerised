<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class webshop_cart_model extends Banshee\model {
		public function get_articles($article_ids) {
			if (is_array($article_ids) == false) {
				return array();
			} else if (count($article_ids) == 0) {
				return array();
			}

			$query = "select * from shop_articles where id in (".
				implode(", ", array_fill(0, count($article_ids), "%d")).
				") order by title";

			if (($articles = $this->db->execute($query, $article_ids)) === false) {
				return false;
			}

			foreach ($articles as $key => $article) {
				if (is_true($article["visible"])) {
					$a = &$_SESSION["webshop_cart"][$article["id"]];
					if ($article["price"] < $a["price"]) {
						$a["price"] = $article["price"];
						$this->view->add_system_message("The price of the ".$article["title"]." was lowered after you placed it in your shopping cart. Its price has been lowered accordingly.");
					} else {
						$articles[$key]["price"] = $a["price"];
					}

					$articles[$key]["quantity"] = $a["quantity"];
					unset($a);
				} else {
					/* Remove hidden article
					 */
					unset($_SESSION["webshop_cart"][$article["id"]]);
					unset($articles[$key]);
					$this->view->add_system_message("The ".$article["title"] ." has been removed from your shopping cart, as it is no longer available.");
				}

				$article_ids = array_diff($article_ids, array($article["id"]));
			}

			foreach ($article_ids as $article_id) {
				/* Remove deleted article
				 */
				unset($_SESSION["webshop_cart"][$article_id]);
				foreach ($articles as $key => $article) {
					if ($article["id"] == $article_id) {
						unset($articles[$key]);
					}
				}
				$this->view->add_system_message("An article has been removed from your shopping cart, as it is no longer available.");
			}

			return $articles;
		}

		public function get_article($article_id) {
			return $this->borrow("webshop")->get_article($article_id);
		}
	}
?>
