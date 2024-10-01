<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_link_category_model extends Banshee\tablemanager_model {
		protected $table = "link_categories";
		protected $order = "category";
		protected $elements = array(
			"category" => array(
				"label"    => "Category",
				"type"     => "varchar",
				"overview" => true,
				"unique"   => true,
				"required" => true),
			"description" => array(
				"label"    => "Description",
				"type"     => "text"));

		public function delete_okay($category_id) {
			$query = "select count(*) as count from links where category_id=%d";

			if (($result = $this->db->execute($query, $category_id)) === false) {
				$this->view->add_system_warning("Database error.");
				return false;
			}

			if ($result[0]["count"] > 0){
				$this->view->add_message("This category is in use.");
				return false;
			}

			return true;
		}
	}
?>
