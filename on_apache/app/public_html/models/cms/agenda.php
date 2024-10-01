<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_agenda_model extends Banshee\model {
		public function get_appointments() {
			$query = "select * from agenda order by begin,end";

			return $this->db->execute($query);
		}

		public function get_appointment($appointment_id) {
			$query = "select * from agenda where id=%d";

			if (($result = $this->db->execute($query, $appointment_id)) == false) {
				return false;
			}

			return $result[0];
		}

		public function appointment_okay($appointment) {
			$result = true;

			if (valid_date($appointment["begin"]) == false) {
				$this->view->add_message("Invalid start time.");
				$result = false;
			} else if (trim($appointment["end"]) != "") {
				if (valid_date($appointment["end"]) == false) {
					$this->view->add_message("Invalid end time.");
					$result = false;
				} else if (strtotime($appointment["begin"]) > strtotime($appointment["end"])) {
					$this->view->add_message("Begin date must lie before end date.");
					$result = false;
				}
			}

			if (trim($appointment["title"]) == "") {
				$this->view->add_message("Empty short description not allowed.");
				$result = false;
			}

			return $result;
		}

		public function create_appointment($appointment) {
			$keys = array("id", "begin", "end", "title", "content");
			$appointment["id"] = null;

			if ($appointment["end"] == "") {
				$appointment["end"] = null;
			}

			return $this->db->insert("agenda", $appointment, $keys) !== false;
		}

		public function update_appointment($appointment) {
			$keys = array("begin", "end", "title", "content");

			if ($appointment["end"] == "") {
				$appointment["end"] = null;
			}

			return $this->db->update("agenda", $appointment["id"], $appointment, $keys) !== false;
		}

		public function delete_appointment($appointment_id) {
			return $this->db->delete("agenda", $appointment_id);
		}
	}
?>
