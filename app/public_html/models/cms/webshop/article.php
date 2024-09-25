<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_webshop_article_model extends Banshee\model {
		private $order_columns = array("article_nr", "title", "category", "price");
		private $search_columns = array("article_nr", "title", "category", "short_description", "long_description", "price");

		public function count_articles() {
			$query = "select count(*) as count from shop_articles";

			if (($result = $this->db->execute($query)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_articles($offset, $limit) {
			if (isset($_SESSION["article_order"]) == false) {
				$_SESSION["article_order"] = array("title", "price");
			}

			if (isset($_GET["order"])) {
				if ((in_array($_GET["order"], $this->order_columns)) && ($_GET["order"] != $_SESSION["article_order"][0])) {
					$_SESSION["article_order"] = array($_GET["order"], $_SESSION["article_order"][0]);
				}
			}

			$query = "select a.*, c.name as category from shop_articles a, shop_categories c ".
			         "where a.shop_category_id=c.id";

			$search = array();
			if ($_SESSION["article_search"] != null) {
				foreach ($this->search_columns as $i => $column) {
					$this->search_columns[$i] = $column." like %s";
					array_push($search, "%".$_SESSION["article_search"]."%");
				}
				$query .= " having (".implode(" or ", $this->search_columns).")";
			}

			$query .= " order by %S,%S limit %d,%d";

			return $this->db->execute($query, $search, $_SESSION["article_order"], $offset, $limit);
		}

		public function get_article($article_id) {
			return $this->db->entry("shop_articles", $article_id);
		}

		public function get_categories() {
			return $this->db->execute("select * from shop_categories order by name");
		}

		public function save_okay($article) {
			$result = true;

			if (trim($article["article_nr"]) == "") {
				$this->view->add_message("Fill in the article number.");
				$result = false;
			}

			if (trim($article["title"]) == "") {
				$this->view->add_message("Specify the article title.");
				$result = false;
			}

			if (preg_match('/^[0-9]+(\.[0-9]{2})?$/', $article["price"]) !== 1) {
				$this->view->add_message("Specify a valid article price.");
				$result = false;
			}

			return $result;
		}

		public function create_article($article) {
			$keys = array("id", "shop_category_id", "article_nr", "title", "short_description", "long_description", "image", "price", "visible");

			$article["id"] = null;
			$article["visible"] = is_true($article["visible"]) ? YES : NO;

			return $this->db->insert("shop_articles", $article, $keys);
		}

		public function update_article($article) {
			$keys = array("shop_category_id", "article_nr", "title", "short_description", "long_description", "image", "price", "visible");

			$article["visible"] = is_true($article["visible"]) ? YES : NO;

			return $this->db->update("shop_articles", $article["id"], $article, $keys);
		}

		public function delete_okay($article) {
			$result = true;

			$query = "select count(*) as count from shop_order_article where shop_article_id=%d";
			if (($result = $this->db->execute($query, $article["id"])) === false) {
				$result = false;
			} else if ($result[0]["count"] > 0) {
				$this->view->add_message("Can't delete this article, because it's currently being ordered.");
				$result = false;
			}

			return $result;
		}

		public function delete_article($article_id) {
			return $this->db->delete("shop_articles", $article_id);
		}
	}
?>
