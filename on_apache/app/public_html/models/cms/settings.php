<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_settings_model extends Banshee\tablemanager_model {
		protected $table = "settings";
		protected $order = "key";
		protected $elements = array(
			"key" => array(
				"label"    => "Key",
				"type"     => "varchar",
				"unique"   => true,
				"overview" => true,
				"required" => true),
			"type" => array(
				"label"    => "Type",
				"type"     => "enum",
				"options"  => array(),
				"default"  => "string",
				"overview" => true),
			"value" => array(
				"label"    => "Value",
				"type"     => "varchar",
				"overview" => true,
				"required" => false));
		private $hidden_keys = array("database_version", "secret_website_code");

		public function __construct() {
			$arguments = func_get_args();
			call_user_func_array(array(parent::class, "__construct"), $arguments);

			$types = $this->settings->supported_types();
			sort($types);
			foreach ($types as $type) {
				$this->elements["type"]["options"][$type] = $type;
			}

			if (is_false(DEBUG_MODE)) {
				$this->elements["key"]["readonly"] = true;
				$this->elements["type"]["readonly"] = true;
				$this->allow_create = false;
				$this->allow_delete = false;

				/* Hide variables
				 */
				$query = "select %S from settings where type=%s";
				if (($keys = $this->db->execute($query, "key", "variable")) != false) {
					foreach ($keys as $key) {
						array_push($this->hidden_keys, $key["key"]);
					}
				}
			}
		}

		public function count_items() {
			$query = "select count(*) as count from %S";
			$args = array($this->table);

			if (count($this->hidden_keys) > 0) {
				$query .= " where %S not in (".
				          implode(", ", array_fill(1, count($this->hidden_keys), "%s")).
				          ")";
				array_push($args, "key", $this->hidden_keys);
			}

			if (($result = $this->db->execute($query, $args)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_items() {
			list($offset, $count) = func_get_args();

			$query = "select * from %S";
			$args = array($this->table);

			if (count($this->hidden_keys) > 0) {
				$query .= " where %S not in (".
				          implode(", ", array_fill(0, count($this->hidden_keys), "%s")).
				          ")";
				array_push($args, "key", $this->hidden_keys);
			}

			$query .= " order by %S limit %d,%d";
			array_push($args, $this->order, $offset, $count);

			return $this->db->execute($query, $args);
		}

		public function get_item($item_id) {
			if (($item = parent::get_item($item_id)) != false) {
				if (in_array($item["key"], $this->hidden_keys)) {
					return false;
				}
			}

			return $item;
		}

		public function save_okay($item) {
			if (in_array($item["key"], $this->hidden_keys)) {
				$this->view->add_message("You are not allowed to change this setting.");
				return false;
			}

			header("X-Hiawatha-Cache-Remove: all");

			$result = parent::save_okay($item);

			return $result;
		}

		public function delete_okay($item_id) {
			if (parent::delete_okay($item_id) == false) {
				return false;
			}

			if (($item = $this->db->entry("settings", $item_id)) == false) {
				return false;
			}

			if (in_array($item["key"], $this->hidden_keys)) {
				$this->view->add_message("You are not allowed to delete this setting.");
				return false;
			}

			return true;
		}

		private function fix_key_type($item) {
			switch ($item["type"]) {
				case "boolean": $item["value"] = is_true($item["value"]) ? "true" : "false"; break;
				case "float": $item["value"] = rtrim((float)$item["value"], "0"); break;
				case "integer": $item["value"] = (int)$item["value"]; break;
			}

			return $item;
		}

		public function create_item($item) {
			$item = $this->fix_key_type($item);
			return parent::create_item($item);
		}

		public function update_item($item) {
			$item = $this->fix_key_type($item);
			return parent::update_item($item);
		}
	}
?>
