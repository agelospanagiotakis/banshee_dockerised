<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_forum_element_model extends Banshee\tablemanager_model {
		protected $table = "forums";
		protected $order = "order";
		protected $manual_order = false;
		protected $elements = array(
			"title" => array(
				"label"    => "Title",
				"type"     => "varchar",
				"overview" => true,
				"required" => true),
			"section" => array(
				"label"    => "Section",
				"placeholder" => "Leave empty to include in previous section",
				"type"     => "varchar",
				"overview" => true,
				"required" => false),
			"description" => array(
				"label"    => "Description",
				"type"     => "text",
				"overview" => false,
				"required" => true),
			"order" => array(
				"label"    => "Order",
				"type"     => "integer",
				"readonly" => true),
			"private" => array(
				"label"    => "Private",
				"type"     => "boolean",
				"overview" => true),
			"required_role_id" => array(
				"label"    => "Required role",
				"type"     => "foreignkey",
				"table"    => "roles",
				"column"   => "name",
				"overview" => true));

		public function create_item($item) {
			if ($item["required_role_id"] != null) {
				$item["private"] = YES;
			}

			return parent::create_item($item);
		}

		public function update_item($item) {
			if ($item["required_role_id"] != null) {
				$item["private"] = YES;
			}

			return parent::update_item($item);
		}

		public function delete_okay($section_id) {
			$query = "select count(*) as count from forum_topics where forum_id=%d";
			if (($section = $this->db->execute($query, $section_id)) === false) {
				return false;
			}

			if ($section[0]["count"] > 0) {
				$this->view->add_message("This forum section contains topics.");
				return false;
			}

			return true;
		}

		public function save_sorting($order) {
			$query = "update forums set %S=%d where id=%d";

			foreach ($order as $number => $id) {
				$this->db->query($query, "order", $number, $id);
			}
		}
	}
?>
