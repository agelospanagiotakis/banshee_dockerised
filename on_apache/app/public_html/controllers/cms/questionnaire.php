<?php
	class cms_questionnaire_controller extends Banshee\controller {
		/* Show questionnaire overview
		 */
		private function show_overview() {
			if (($questionnaire_count = $this->model->count_questionnaires()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$pagination = new \Banshee\pagination($this->view, "questionnaires", $this->settings->admin_page_size, $questionnaire_count);

			if ($questionnaire_count == 0) {
				$questionnaires = array();
			} else if (($questionnaires = $this->model->get_questionnaires($pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$this->view->open_tag("overview");

			$this->view->open_tag("questionnaires");
			foreach ($questionnaires as $questionnaire) {
				$questionnaire["active"] = show_boolean($questionnaire["active"]);
				$this->view->record($questionnaire, "questionnaire");
			}
			$this->view->close_tag();

			$pagination->show_browse_links();

			$this->view->close_tag();

			$_SESSION["questionnaire_filter"] = array();
		}

		/* Show questionnaire form
		 */
		private function show_questionnaire_form($questionnaire) {
			$this->view->add_help_button();

			$this->view->open_tag("edit");
			$questionnaire["active"] = show_boolean($questionnaire["active"] ?? false);
			$questionnaire["activated"] = show_boolean($questionnaire["activated"]);
			$this->view->record($questionnaire, "questionnaire");
			$this->view->close_tag();
		}

		/* Show questionnaire results
		 */
		private function show_questionnaire_results($questionnaire_id) {
			if (valid_input($questionnaire_id, VALIDATE_NUMBERS, VALIDATE_NONEMPTY) == false) {
				$this->show_overview();
				return;
			}

			if (($questionnaire = $this->model->get_questionnaire($questionnaire_id)) == false) {
				$this->view->add_tag("result", "Questionnaire not found.");
				return;
			}
			$questionnaire["form"] = $this->model->parse_form($questionnaire["form"]);

			if (($submits = $this->model->get_answers($questionnaire_id)) == false) {
				$this->view->add_tag("result", "Error loading answers.");
				return;
			}

			if (is_array($_SESSION["questionnaire_filter"]) == false) {
				$_SESSION["questionnaire_filter"] = array();
			}

			$answers = array();
			foreach ($submits as $submit) {
				$answer = json_decode($submit["answers"], true);

				foreach ($_SESSION["questionnaire_filter"] as $key => $value) {
					if ($answer[$key] != $value) {
						continue 2;
					}
				}

				array_push($answers, $answer);
			}

			$answer_count = count($answers);

			$this->view->open_tag("answers", array("id" => $questionnaire_id, "count" => $answer_count));

			foreach ($questionnaire["form"] as $number => $block) {
				$question = array_shift($block);
				$type = array_shift($block);
				$parts = explode(" ", $type);
				$type = $parts[0];
				$required = $parts[1] ?? null;
				$name = "f".$number;

				/* Show filter
				 */
				if ($type == "select") {
					$this->view->open_tag("filter", array("name" => $name));
					$this->view->add_tag("question", $question);
					foreach ($block as $number => $option) {
						$selected = ($_SESSION["questionnaire_filter"][$name] ?? null) === (string)$number;
						$attr = array("selected" => show_boolean($selected));
						$this->view->add_tag("option", $option, $attr);
					}
					$this->view->close_tag();
				}

				/* Show question
				 */
				$this->view->open_tag("question", array("type" => $type, "name" => $name));
				$this->view->add_tag("text", $question);

				switch ($type) {
					case "line":
					case "text":
						foreach ($answers as $answer) {
							$this->view->add_tag("answer", $answer[$name]);
						}
						break;
					case "radio":
						foreach ($block as $number => $option) {
							if ($option == "other") {
								foreach ($answers as $answer) {
									if ($answer[$name] == $number) {
										$this->view->add_tag("other", $answer[$name."_other"]);
									}
								}
							}
						}
					case "select":
					case "checkbox":
						$stats = array();
						$stats[""] = 0;
						foreach ($block as $number => $line) {
							$stats[$number] = 0;
						}

						foreach ($answers as $answer) {
							if ($type == "checkbox") {
								if ($answer[$name] == null) {
									continue;
								}
								$answer[$name] = explode(",", $answer[$name]);
								foreach ($answer[$name] as $option) {
									$stats[$option]++;
								}
							} else {
								$stats[$answer[$name]]++;
							}
						}

						foreach ($block as $number => $option) {
							$perc = ($answer_count > 0) ? round(100 * $stats[$number] / $answer_count) : 0;
							$attr = array(
								"count" => $stats[$number],
								"perc"  => $perc);
							$this->view->add_tag("option", $option, $attr);
						}
						break;
				}

				$this->view->close_tag();
			}

			$this->view->close_tag();
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Save questionnaire") {
					/* Save questionnaire
					 */
					if ($this->model->save_okay($_POST) == false) {
						$this->show_questionnaire_form($_POST);
					} else if (isset($_POST["id"]) === false) {
						/* Create questionnaire
						 */
						if ($this->model->create_questionnaire($_POST) === false) {
							$this->view->add_message("Error creating questionnaire.");
							$this->show_questionnaire_form($_POST);
						} else {
							$this->user->log_action("questionnaire %d created", $this->db->last_insert_id);
							$this->show_overview();
						}
					} else {
						/* Update questionnaire
						 */
						if ($this->model->update_questionnaire($_POST) === false) {
							$this->view->add_message("Error updating questionnaire.");
							$this->show_questionnaire_form($_POST);
						} else {
							$this->user->log_action("questionnaire %d updated", $_POST["id"]);
							$this->show_overview();
						}
					}
				} else if ($_POST["submit_button"] == "Erase answers") {
					if ($this->model->erase_answers($_POST["id"]) === false) {
						$this->view->add_message("Error erasing answers.");
						$this->show_questionnaire_form($_POST);
					} else {
						$this->view->add_message("The answers for this questionnaire have been erased.");
						$this->user->log_action("questionnaire answers %d deleted", $_POST["id"]);
						$_POST["answers"] = 0;
						$this->show_questionnaire_form($_POST);
					}
				} else if ($_POST["submit_button"] == "Delete questionnaire") {
					/* Delete questionnaire
					 */
					if ($this->model->delete_questionnaire($_POST["id"]) === false) {
						$this->view->add_message("Error deleting questionnaire.");
						$this->show_questionnaire_form($_POST);
					} else {
						$this->user->log_action("questionnaire %d deleted", $_POST["id"]);
						$this->show_overview();
					}
				} else if ($_POST["submit_button"] == "Filter") {
					/* Filter
					 */
					if (is_array($_SESSION["questionnaire_filter"]) == false) {
						$_SESSION["questionnaire_filter"] = array();
					}

					if (is_array($_POST["filter"])) {
						foreach ($_POST["filter"] as $key => $value) {
							if ($value != "") {
								$_SESSION["questionnaire_filter"][$key] = $value;
							} else {
								unset($_SESSION["questionnaire_filter"][$key]);
							}
						}
					}

					$this->show_questionnaire_results($this->page->parameters[1]);
				} else {
					$this->show_overview();
				}
			} else if ($this->page->parameter_value(0, "new")) {
				/* New questionnaire
				 */
				$questionnaire = array(
					"answers"   => 0,
					"activated" => NO,
					"submit"    => "Submit",
					"after"     => "\n\n\n<div class=\"btn-group\">\n<a href=\"/\" class=\"btn btn-default\">Back</a>\n</div>");
				$this->show_questionnaire_form($questionnaire);
			} else if ($this->page->parameter_value(0, "view")) {
				$this->show_questionnaire_results($this->page->parameters[1]);
			} else if ($this->page->parameter_numeric(0)) {
				/* Edit questionnaire
				 */
				if (($questionnaire = $this->model->get_questionnaire($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "questionnaire not found.");
				} else {
					$questionnaire["activated"] = $questionnaire["active"];
					$this->show_questionnaire_form($questionnaire);
				}
			} else {
				/* Show overview
				 */
				$this->show_overview();
			}
		}
	}
?>
