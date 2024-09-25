<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_webshop_category_model extends Banshee\tablemanager_model {
		protected $table = "shop_categories";
		protected $order = "name";
		protected $elements = array(
			"name" => array(
				"label"    => "Name",
				"type"     => "varchar",
				"overview" => true,
				"required" => true));

		public function delete_okay($category_id) {
			$query = "select count(*) as count from shop_articles where shop_category_id=%d";
			if (($result = $this->db->execute($query, $category_id)) === false) {
				$this->view->add_system_warning("Database error.");
				return false;
			}

			if ($result[0]["count"] > 0) {
				$this->view->add_message("This category contains articles.");
				return false;
			}

			return true;
		}
	}
?>
