<?php
	class cms_questionnaire_model extends Banshee\model {
		const OPTION_TYPES = array("checkbox", "radio", "select");

		public function count_questionnaires() {
			$query = "select count(*) as count from questionnaires";

			if (($result = $this->db->execute($query)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_questionnaires($offset = null, $limit = null) {
			$query = "select id, title, active, (select count(*) from questionnaire_answers where questionnaire_id=q.id) ".
			         "as answers from questionnaires q order by id desc ";

			if ($offset !== null) {
				$query .= " limit %d,%d";
			}

			return $this->db->execute($query, $offset, $limit);
		}

		public function get_questionnaire($questionnaire_id) {
			static $cache = array();

			if (isset($cache[$questionnaire_id]) == false) {
				$query = "select *, (select count(*) from questionnaire_answers where questionnaire_id=q.id) ".
				         "as answers from questionnaires q where id=%d";
				if (($result = $this->db->execute($query, $questionnaire_id)) == false) {
					return false;
				}

				$cache[$questionnaire_id] = $result[0];
			}

			return $cache[$questionnaire_id];
		}

		public function parse_form($form) {
			return $this->borrow("questionnaire")->parse_form($form);
		}

		public function get_answers($questionnaire_id) {
			$query = "select * from questionnaire_answers where questionnaire_id=%d";

			return $this->db->execute($query, $questionnaire_id);
		}

		public function save_okay($questionnaire) {
			if (isset($questionnaire["id"]) == false) {
				$current = array("answers" => 0);
			} else if (($current = $this->get_questionnaire($questionnaire["id"])) == false) {
				return false;
			}

			$result = true;

			if (trim($questionnaire["title"]) == "") {
				$this->view->add_message("The title can't be empty.");
				$result = false;
			}

			if (is_false($current["active"] ?? false) && ($current["answers"] == 0)) {
				if (trim($questionnaire["form"]) == "") {
					$this->view->add_message("The form can't be empty.");
					$result = false;
				} else {
					$form = $this->parse_form($questionnaire["form"]);
					foreach ($form as $number => $block) {
						$question = array_shift($block);
						$type = array_shift($block);
						if ($type == null) {
							continue;
						}

						$parts = explode(" ", $type);
						$type = $parts[0];
						$required = $parts[1] ?? "";
						$name = "f".$number;

						if ($type == "radio") {
							$value_count = array_count_values($block);
							if (($value_count["other"] ?? 0) > 1) {
								$this->view->add_message("Two or more 'other' present in question %s.", $question);
								$result = false;
							}
						}

						if (in_array($type, array("line", "text"))) {
							if (count($block) > 0) {
								$this->view->add_message("Unnecessary options for question %s.", $question);
								$result = false;
							}
						} else if (in_array($type, self::OPTION_TYPES)) {
							if (count($block) == 0) {
								$this->view->add_message("Options missing for question %s.", $question);
								$result = false;
							}
						} else {
							$this->view->add_message("Invalid type for question %s.", $question);
							$result = false;
						}

						if (($required != "") && ($required != "required")) {
							$this->view->add_message("Syntax error in second line of question %s.", $question);
							$result = false;
						}
					}
				}
			}

			if (trim($questionnaire["submit"]) == "") {
				$this->view->add_message("The submit button can't be empty.");
				$result = false;
			}

			if (trim($questionnaire["after"]) == "") {
				$this->view->add_message("The text after submit can't be empty.");
				$result = false;
			}

			return $result;
		}

		public function create_questionnaire($questionnaire) {
			$keys = array("id", "title", "intro", "form", "submit", "after", "active", "access_code");

			$questionnaire["id"] = null;
			$questionnaire["active"] = is_true($questionnaire["active"] ?? false) ? YES : NO;

			return $this->db->insert("questionnaires", $questionnaire, $keys);
		}

		public function update_questionnaire($questionnaire) {
			if (($current = $this->get_questionnaire($questionnaire["id"])) == false) {
				return false;
			}

			$keys = array("title", "intro", "submit", "after", "active", "access_code");

			if (is_false($current["active"]) && ($current["answers"] == 0)) {
				array_push($keys, "form");
			}

			$questionnaire["active"] = is_true($questionnaire["active"] ?? false) ? YES : NO;

			return $this->db->update("questionnaires", $questionnaire["id"], $questionnaire, $keys);
		}

		public function erase_answers($questionnaire_id) {
			$query = "delete from questionnaire_answers where questionnaire_id=%d";

			return $this->db->query($query, $questionnaire_id) !== false;
		}

		public function delete_questionnaire($questionnaire_id) {
			$queries = array(
				array("delete from questionnaire_answers where questionnaire_id=%d", $questionnaire_id),
				array("delete from questionnaires where id=%d", $questionnaire_id));

			return $this->db->transaction($queries);
		}
	}
?>
