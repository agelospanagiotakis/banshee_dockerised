<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class questionnaire_model extends Banshee\model {
		public function get_questionnaires() {
			$query = "select id, title, active from questionnaires order by title";

			return $this->db->execute($query);
		}

		public function parse_form($form) {
			$lines = explode("\n", $form);
			$blocks = $block = array();

			foreach ($lines as $line) {
				$line = trim($line);

				if ($line == "") {
					if (count($block) != 0) {
						array_push($blocks, $block);
						$block = array();
					}
				} else {
					array_push($block, $line);
				}
			}

			if (count($block) != 0) {
				array_push($blocks, $block);
			}

			return $blocks;
		}

		public function get_questionnaire($questionnaire_id) {
			static $cache = array();

			if (isset($cache[$questionnaire_id]) == false) {
				if (($questionnaire = $this->db->entry("questionnaires", $questionnaire_id)) == false) {
					return false;
				}

				$questionnaire["form"] = $this->parse_form($questionnaire["form"]);

				$cache[$questionnaire_id] = $questionnaire;
			}

			return $cache[$questionnaire_id];
		}

		public function questionnaire_okay($answers) {
			if (($questionnaire = $this->get_questionnaire($answers["id"])) == false) {
				return false;
			}

			$result = true;

			$missing = array();
			foreach ($questionnaire["form"] as $number => $block) {
				if (substr($block[0], -1) == ":") {
					$block[0] = rtrim($block[0], ":").".";
				}

				$parts = explode(" ", $block[1]);
				$type = $parts[0];
				$required = $parts[1] ?? null;
				$name = "f".$number;

				if (($type == "radio") && ($block[($answers[$name] ?? 0) + 2] == "other")) {
					$answer = $answers[$name."_other"];

					if (trim($answer) == "") {
						$this->view->add_message("Fill in the 'Other' option for %s", $block[0]);
						$result = false;
					}
				} else {
					$answer = $answers[$name] ?? "";
				}

				if ($required == "required") {
					if (is_array($answer)) {
						if (count($answer) == 0) {
							array_push($missing, $block[0]);
						}
					} else if (trim($answer) == "") {
						array_push($missing, $block[0]);
					}
				}
			}
			if (count($missing) > 0) {
				$this->view->add_message("Please, answer the following questions:");
				foreach ($missing as $question) {
					$this->view->add_message("- ".$question);
				}
				$result = false;
			}

			return $result;
		}

		public function questionnaire_active($questionnaire_id) {
			if (($questionnaire = $this->get_questionnaire($questionnaire_id)) == false) {
				return false;
			}

			return is_true($questionnaire["active"]);
		}

		public function get_data($answers) {
			if (($questionnaire = $this->get_questionnaire($answers["id"])) == false) {
				return false;
			}

			$data = array();

			foreach ($questionnaire["form"] as $number => $block) {
				array_shift($block);
				$type = array_shift($block);
				$parts = explode(" ", $type);
				$type = $parts[0];
				$required = $parts[0] ?? null;
				$name = "f".$number;

				$data[$name] = $answers[$name] ?? "";

				if (($type == "radio") && isset($answers[$name])) {
					if ($block[$answers[$name]] == "other") {
						$other = $name."_other";
						$data[$other] = $answers[$other];
					}
				}
			}

			return $data;
		}

		public function save_answers($answers) {
			if (($questionnaire = $this->get_questionnaire($answers["id"])) == false) {
				return false;
			}

			$data["id"] = null;
			$data["questionnaire_id"] = $answers["id"];
			$data["ip_addr"] = $_SERVER["REMOTE_ADDR"];

			foreach ($questionnaire["form"] as $number => $block) {
				$question = array_shift($block);
				$type = array_shift($block);
				$parts = explode(" ", $type);
				$type = $parts[0];
				$required = $parts[1] ?? null;
				$name = "f".$number;

				if (($type == "checkbox") && isset($answers[$name])) {
					if (is_array($answers[$name])) {
						$answers[$name] = implode(",", array_keys($answers[$name]));
					}
				}
			}

			if (($data["answers"] = json_encode($this->get_data($answers))) == false) {
				return false;
			}

			return $this->db->insert("questionnaire_answers", $data) !== false;
		}
	}
?>
