<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_action_model extends Banshee\model {
		private $log = null;

		private function read_logfile() {
			if ($this->log !== null) {
				return;
			}

			$this->log = array();

			if (($fp = fopen("../logfiles/actions.log", "r")) == false) {
				return false;
			}

			while (($line = fgets($fp)) != false) {
				array_unshift($this->log, trim($line));
			}

			fclose($fp);
		}

		public function get_log_size() {
			$this->read_logfile();

			return count($this->log);
		}

		public function get_action_log($offset, $size) {
			$this->read_logfile();

			$log = array_slice($this->log, $offset, $size);

			foreach ($log as $i => $line) {
				$entry = explode("|", $line);
				$log[$i] = array(
					"ip"        => $entry[0],
					"timestamp" => $entry[1],
					"path"      => $entry[2],
					"user_id"   => $entry[3],
					"event"     => $entry[4] ?? "");
			}

			return $log;
		}

		public function get_user($user_id) {
			return $this->db->entry("users", $user_id);
		}
	}
?>
