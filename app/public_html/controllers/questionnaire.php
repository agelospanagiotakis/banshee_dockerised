<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class questionnaire_controller extends Banshee\controller {
		const OPTION_TYPES = array("checkbox", "radio", "select");

		protected $title = "Questionnaire";

		private function show_questionnaires() {
			if (($questionnaires = $this->model->get_questionnaires()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$maintainer = $this->user->access_allowed("cms/questionnaire");

			$this->view->open_tag("overview");

			foreach ($questionnaires as $questionnaire) {
				if (is_false($questionnaire["active"]) && ($maintainer == false)) {
					continue;
				}

				$this->view->add_tag("questionnaire", $questionnaire["title"], array("id" => $questionnaire["id"]));
			}

			$this->view->close_tag();
		}

		private function questionnaire_form($questionnaire_id, $values = array()) {
			if (($questionnaire = $this->model->get_questionnaire($questionnaire_id)) == false) {
				$this->view->add_tag("result", "Questionnaire not found.");
				return;
			}

			$maintainer = $this->user->access_allowed("cms/questionnaire");

			if (is_false($questionnaire["active"])) {
				if ($maintainer == false) {
					$this->view->add_tag("result", "Questionnaire not found.");
					return;
				} else {
					$this->view->add_system_message("Questionnaire in test modus.");
				}
			}

			if ($questionnaire["access_code"] != "") {
				if ($questionnaire["access_code"] != ($_SESSION["access_code"] ?? null)) {
					if ($_SERVER["REQUEST_METHOD"] == "POST") {
						$this->view->add_message("Invalid access code.");
					}
					$this->view->add_tag("access_code");
					return;
				}
			}

			$this->view->title = $questionnaire["title"];

			$this->view->open_tag("questionnaire", array("id" => $questionnaire["id"]));

			$this->view->add_tag("intro", $questionnaire["intro"]);
			$this->view->add_tag("submit", $questionnaire["submit"]);

			foreach ($questionnaire["form"] as $number => $block) {
				$question = array_shift($block);
				$type = array_shift($block);
				$parts = explode(" ", $type);
				$type = $parts[0];
				$required = $parts[1] ?? null;
				$name = "f".$number;

				$this->view->open_tag("input", array(
					"type"     => $type,
					"name"     => $name,
					"required" => show_boolean($required == "required")));
				$this->view->add_tag("question", $question);

				if ($type != "checkbox") {
					$this->view->add_tag("value", $values[$name] ?? "");
				} else if (is_array($values[$name] ?? null) == false) {
					$values[$name] = array();
				}

				if (in_array($type, self::OPTION_TYPES)) {
					foreach ($block as $number => $option) {
						$attr = array();
						if ($type == "checkbox") {
							$attr["checked"] = show_boolean($values[$name][$number] ?? false);
						}
						if (($type == "radio") && ($option == "other")) {
							$attr["text"] = $values[$name."_other"] ?? "";
						}
						$this->view->add_tag("option", $option, $attr);
					}
				}
				$this->view->close_tag();
			}

			$this->view->close_tag();
		}

		private function show_answers($answers) {
			if (($questionnaire = $this->model->get_questionnaire($answers["id"])) == false) {
				$this->view->add_tag("result", "Questionnaire not found.");
				return;
			}

			if (($data = $this->model->get_data($answers)) == false) {
				return;
			}

			$this->view->title = $questionnaire["title"]." result";

			$this->view->open_tag("answers", array("id" => $answers["id"]));

			$this->view->add_tag("after", $questionnaire["after"]);

			$block_nr = 0;
			$skip = array();
			foreach ($data as $key => $value) {
				if (in_array($key, $skip)) {
					continue;
				}

				$this->view->open_tag("answer");

				$this->view->add_tag("question", $questionnaire["form"][$block_nr][0]);

				$parts = explode(" ", $questionnaire["form"][$block_nr][1]);
				$type = $parts[0];
				$name = $parts[1] ?? null;

				if ($type == "checkbox") {
					if (is_array($value)) {
						$options = array();
						foreach (array_keys($value) as $key) {
							array_push($options, $questionnaire["form"][$block_nr][$key + 2]);
						}
						$value = implode(", ", $options);
					}
				} else if (in_array($type, self::OPTION_TYPES)) {
					$orig = $value;
					if (($value !== "") && ($value !== null)) {
						$value = $questionnaire["form"][$block_nr][$value + 2];
					}
					if (($type == "radio") && ($value == "other")) {
						$other = $key."_other";
						$value = $data[$other];
						array_push($skip, $other);
					}
				}

				$this->view->add_tag("answer", $value);

				$this->view->close_tag();

				$block_nr++;
			}

			$this->view->close_tag();
		}

		private function show_result($questionnaire_id) {
			if (($questionnaire = $this->model->get_questionnaire($questionnaire_id)) == false) {
				$this->view->add_tag("result", "Questionnaire not found.");
				return;
			}

			$this->view->title = $questionnaire["title"];

			$this->view->add_tag("after", $questionnaire["after"]);
		}

		public function execute() {
			$this->view->title = $this->title;

			if ($this->page->parameter_numeric(0)) {
				if ($_SERVER["REQUEST_METHOD"] == "POST") {
					if (isset($_POST["access_code"])) {
						$_SESSION["access_code"] = $_POST["access_code"];
						$this->questionnaire_form($this->page->parameters[0]);
					} else if ($this->model->questionnaire_okay($_POST) == false) {
						$this->questionnaire_form($this->page->parameters[0], $_POST);
					} else if ($this->model->questionnaire_active($_POST["id"]) == false) {
						$this->show_answers($_POST);
					} else {
						$this->model->save_answers($_POST);
						$this->show_result($_POST["id"]);
					}
				} else {
					$this->questionnaire_form($this->page->parameters[0]);
				}
			} else {
				$this->show_questionnaires();
			}
		}
	}
?>
