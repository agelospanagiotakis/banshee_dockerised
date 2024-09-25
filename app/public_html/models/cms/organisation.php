<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_organisation_model extends Banshee\model {
		public function count_organisations() {
			$query = "select count(*) as count from organisations";

			if (($result = $this->db->execute($query)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_organisations($offset, $limit) {
			$query = "select * from organisations order by name limit %d,%d";

			return $this->db->execute($query, $offset, $limit);
		}

		public function get_organisation($organisation_id) {
			return $this->db->entry("organisations", $organisation_id);
		}

		public function get_users($organisation_id) {
			$query = "select * from users where organisation_id=%d order by fullname";

			return $this->db->execute($query, $organisation_id);
		}

		public function save_okay($organisation) {
			$result = true;

			if (isset($organisation["id"]) == false) {
				if (is_true(ENCRYPT_DATA)) {
					$this->view->add_message("Can't create organisations with database encryption enabled.");
					$result = false;
				}
			}

			if (trim($organisation["name"]) == "") {
				$this->view->add_message("Empty name is not allowed.");
				$result = false;
			}

			if (($check = $this->db->entry("organisations", $organisation["name"], "name")) === false) {
				$this->view->add_message("Database error.");
				$result = false;
			} else if ($check != false) {
				if ($check["id"] != $organisation["id"]) {
					$this->view->add_message("Organisation name already exists.");
					$result = false;
				}
			}

			return $result;
		}

		public function create_organisation($organisation, $register = false) {
			$keys = array("id", "name", "invitation_code");

			$organisation["id"] = null;
			$organisation["invitation_code"] = null;

			if ($this->db->insert("organisations", $organisation, $keys) == false) {
				return false;
			}

			$organisation_id = $this->db->last_insert_id;

			return $organisation_id;
		}

		public function update_organisation($organisation) {
			$keys = array("name");

			return $this->db->update("organisations", $organisation["id"], $organisation, $keys);
		}

		public function delete_okay($organisation_id) {
			$query = "select count(*) as count from users where organisation_id=%d";

			if (($result = $this->db->execute($query, $organisation_id)) === false) {
				$this->view->add_system_warming("Database error.");
				return false;
			}

			if ((int)$result[0]["count"] > 0) {
				$this->view->add_message("Organisation in use.");
				return false;
			}

			return true;
		}

		public function delete_organisation($organisation_id) {
			return $this->db->delete("organisations", $organisation_id);
		}
	}
?>
