<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class webshop_model extends Banshee\model {
		public function count_articles($search = null, $category_id = null) {
			$query = "select count(*) as count from shop_articles where visible=%d";
			$args = array(YES);

			if ($search != null) {
				$query .= " and title like %s";
				array_push($args, "%".$search."%");
			}

			if ($category_id != null) {
				$query .= " and shop_category_id=%d";
				array_push($args, $category_id);
			}

			if (($result = $this->db->execute($query, $args)) === false) {
				return false;
			}

			return (int)$result[0]["count"];
		}

		public function get_articles($offset, $limit, $search = null, $category_id = null) {
			$query = "select * from shop_articles where visible=%d";
			$args = array(YES);

			if ($search != null) {
				$query .= " and (title like %s or short_description like %s or long_description like %s)";
				array_push($args, "%".$search."%", "%".$search."%", "%".$search."%");
			}

			if ($category_id != null) {
				$query .= " and shop_category_id=%d";
				array_push($args, $category_id);
			}

			$query .= " order by title limit %d,%d";
			array_push($args, $offset, $limit);

			return $this->db->execute($query, $args);
		}

		public function get_categories() {
			if (($result = $this->db->execute("select * from shop_categories order by name")) === false) {
				return false;
			}

			array_unshift($result, array(
				"id"   => 0,
				"name" => "All"));

			return $result;
		}

		public function get_article($article_id) {
			$query = "select * from shop_articles where id=%d and visible=%d";
			if (($result = $this->db->execute($query, $article_id, YES)) == false) {
				return false;
			}

			return $result[0];
		}
	}
?>
