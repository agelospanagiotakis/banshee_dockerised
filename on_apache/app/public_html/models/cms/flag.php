<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_flag_model extends Banshee\tablemanager_model {
		protected $table = "flags";
		protected $order = array("role_id", "module", "flag");
		protected $module_flags = array();
		protected $elements = array(
			"role_id" => array(
				"label"    => "Role",
				"type"     => "foreignkey",
				"table"    => "roles",
				"column"   => "name",
				"overview" => true,
				"required" => true),
			"module" => array(
				"label"    => "Module",
				"type"     => "enum",
				"options"  => array(),
				"overview" => true,
				"required" => true),
			"flag" => array(
				"label"    => "Flag",
				"type"     => "enum",
				"options"  => array(),
				"overview" => true,
				"required" => true));

		public function __construct() {
			$flags = config_array(MODULE_FLAGS);
			foreach ($flags as $key => $value) {
				$this->module_flags[$key] = explode(",", $value);
				foreach ($this->module_flags[$key] as $value) {
					$this->elements["flag"]["options"][$value] = $value;
				}
			}

			$arguments = func_get_args();
			call_user_func_array(array(parent::class, "__construct"), $arguments);

			$modules = array_keys($this->module_flags);
			sort($modules);

			foreach ($modules as $module) {
				$this->elements["module"]["options"][$module] = $module;
			}
		}

		public function __get($key) {
			switch ($key) {
				case "module_flags": return $this->module_flags;
			}

			return parent::__get($key);
		}

		public function get_flags($module) {
			if (isset($this->module_flags[$module]) == false) {
				return false;
			}

			return $this->module_flags[$module];
		}

		public function get_item($item_id) {
			if (($item = parent::get_item($item_id)) !== false) {
				if (($flags = $this->get_flags($item["module"])) !== false) {
					foreach ($flags as $flag) {
						$this->elements["flag"]["options"][$flag] = $flag;
					}
				}
			}

			return $item;
		}

		public function save_okay($item) {
			$flags = $this->module_flags[$item["module"]];
			foreach ($flags as $flag) {
				$this->elements["flag"]["options"][$flag] = $flag;
			}

			$query = "select * from flags where role_id=%d and module=%s and flag=%s";
			if (($result = $this->db->execute($query, $item["role_id"], $item["module"], $item["flag"])) === false) {
				return false;
			}
			if (count($result) > 0) {
				if ($result[0]["id"] != $item["id"]) {
					$this->view->add_message("This combination already exists.");
					return false;
				}
			}

			return parent::save_okay($item);
		}
	}
?>
