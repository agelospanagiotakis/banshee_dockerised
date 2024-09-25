<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_agenda_controller extends Banshee\controller {
		public function show_agenda_overview() {
			if (($appointments = $this->model->get_appointments()) === false) {
				$this->view->add_tag("result", "Database error");
				return;
			}

			$this->view->open_tag("overview");

			$this->view->open_tag("appointments", array("now" => strtotime("yesterday 23:59:59")));
			foreach ($appointments as $appointment) {
				if ($this->view->mobile == false) {
					$appointment["begin"] = date("l, j F Y", strtotime($appointment["begin"]));
					if ($appointment["end"] != null) {
						$appointment["end"] = date("l, j F Y", strtotime($appointment["end"]));
					}
				}
				$appointment["timestamp"] = strtotime($appointment["begin"]." 00:00:00");
				$this->view->record($appointment, "appointment");
			}
			$this->view->close_tag();

			$this->view->close_tag();
		}

		public function show_appointment_form($appointment) {
			$this->view->add_javascript("webui/jquery-ui.js");
			$this->view->add_javascript("banshee/datepicker.js");
			$this->view->start_ckeditor();

			$this->view->add_css("webui/jquery-ui.css");

			$this->view->record($appointment, "edit");
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Save appointment") {
					/* Save appointment
					 */
					if ($this->model->appointment_okay($_POST) == false) {
						$this->show_appointment_form($_POST);
					} else if (isset($_POST["id"]) == false) {
						/* Create appointment
						 */
						if ($this->model->create_appointment($_POST) == false) {
							$this->view->add_message("Error while creating appointment.");
							$this->show_appointment_form($_POST);
						} else {
							$this->user->log_action("appointment %d created", $this->db->last_insert_id);
							$this->show_agenda_overview();
						}
					} else {
						/* Update appointment
						 */
						if ($this->model->update_appointment($_POST) == false) {
							$this->view->add_message("Error while updating appointment.");
							$this->show_appointment_form($_POST);
						} else {
							$this->user->log_action("appointment %d updated", $_POST["id"]);
							$this->show_agenda_overview();
						}
					}
				} else if ($_POST["submit_button"] == "Delete appointment") {
					/* Delete appointment
					 */
					if ($this->model->delete_appointment($_POST["id"]) == false) {
						$this->view->add_tag("result", "Error while deleting appointment.");
					} else {
						$this->user->log_action("appointment %d deleted", $_POST["id"]);
						$this->show_agenda_overview();
					}
				} else {
					$this->show_agenda_overview();
				}
			} else if ($this->page->parameter_value(0, "new")) {
				/* New appointment
				 */
				$appointment = array(
					"begin" => date("Y-m-d"));
				$this->show_appointment_form($appointment);
			} else if ($this->page->parameter_numeric(0)) {
				/* Edit appointment
				 */
				if (($appointment = $this->model->get_appointment($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "Agendapunten niet gevonden.");
				} else {
					$this->show_appointment_form($appointment);
				}
			} else {
				/* Show month
				 */
				$this->show_agenda_overview();
			}
		}
	}
?>
