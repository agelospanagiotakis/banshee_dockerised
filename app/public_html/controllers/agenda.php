<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class agenda_controller extends Banshee\controller {
		private function show_month($month, $year) {
			if (($appointments = $this->model->get_appointments_for_month($month, $year)) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$day = $this->model->monday_before($month, $year);
			$last_day = $this->model->sunday_after($month, $year);
			$today = strtotime("today 00:00:00");

			$months_of_year = config_array(MONTHS_OF_YEAR);
			$this->view->open_tag("month", array("title" => $months_of_year[$month - 1]." ".$year));

			/* Links
			 */
			$y = $year;
			if (($m = $month - 1) == 0) {
				$m = 12;
				$y--;
			}
			$this->view->add_tag("prev", $y."/".$m);

			$y = $year;
			if (($m = $month + 1) == 13) {
				$m = 1;
				$y++;
			}
			$this->view->add_tag("next", $y."/".$m);

			/* Days of week
			 */
			$days_of_week = config_array(DAYS_OF_WEEK);
			$this->view->open_tag("days_of_week");
			foreach ($days_of_week as $dow) {
				if ($this->view->mobile) {
					$dow = substr($dow, 0, 3);
				}
				$this->view->add_tag("day", $dow);
			}
			$this->view->close_tag();

			/* Weeks
			 */
			while ($day < $last_day) {
				$this->view->open_tag("week");
				for ($dow = 1; $dow <= 7; $dow++) {
					$params = array("nr" => date_string("j", $day), "dow" => $dow);
					if ($day == $today) {
						$params["today"] = " today";
					}
					$this->view->open_tag("day", $params);

					foreach ($appointments as $appointment) {
						if (($appointment["begin"] >= $day) && ($appointment["begin"] < $day + DAY)) {
							$this->view->add_tag("appointment", $appointment["title"], array("id" => $appointment["id"]));
						} else if (($appointment["begin"] < $day) && ($appointment["end"] >= $day)) {
							$this->view->add_tag("appointment", "... ".$appointment["title"], array("id" => $appointment["id"]));
						}
					}
					$this->view->close_tag();

					$day = strtotime(date_string("d-m-Y", $day)." +1 day");
				}
				$this->view->close_tag();
			}
			$this->view->close_tag();
		}

		private function show_appointment($appointment_id) {
			if (($appointment = $this->model->get_appointment($appointment_id)) == false) {
				$this->view->add_tag("result", "Unknown appointment.");
				return;
			}

			$this->view->title = $appointment["title"]." - Agenda";

			$this->show_appointment_record($appointment);
		}

		private function show_appointment_record($appointment) {
			$appointment["begin"] = date_string("l j F Y", $appointment["begin"]);
			if ($appointment["end"] != null) {
				$appointment["end"] = date_string("l j F Y", $appointment["end"]);
			}

			$this->view->record($appointment, "appointment");
		}

		private function show_icalendar() {
			if (($appointments = $this->model->get_appointments_from_today()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			$ical = new \Banshee\Protocol\iCalendar("Agenda", "Banshee//NL");

			foreach ($appointments as $appointment) {
				$ical->add_item($appointment["title"], $appointment["content"], $appointment["begin"], $appointment["end"]);
			}

			$ical->to_view($this->view);
		}

		public function execute() {
			if ($this->page->type == "ics") {
				$this->show_icalendar();
				return;
			}

			$this->view->description = "Agenda";
			$this->view->keywords = "agenda";
			$this->view->title = "Agenda";

			if (isset($_SESSION["calendar_month"]) == false) {
				$_SESSION["calendar_month"] = (int)date_string("m");
				$_SESSION["calendar_year"]  = (int)date("Y");
			}

			if ($this->page->parameter_value(0, "list")) {
				/* Show appointment list
				 */
				if (($appointments = $this->model->get_appointments_from_today()) === false) {
					$this->view->add_tag("result", "Database error.");
				} else {
					$this->view->open_tag("list");
					foreach ($appointments as $appointment) {
						$this->show_appointment_record($appointment);
					}
					$this->view->close_tag();
				}
			} else if ($this->page->parameter_value(0, "current")) {
				/* Show current month
				 */
				$_SESSION["calendar_month"] = (int)date_string("m");
				$_SESSION["calendar_year"]  = (int)date("Y");
				$this->show_month($_SESSION["calendar_month"], $_SESSION["calendar_year"]);
			} else if ($this->page->parameter_numeric(0)) {
				if ($this->page->parameter_numeric(1)) {
					$m = (int)$this->page->parameters[1];
					$y = (int)$this->page->parameters[0];

					if (($m >= 1) && ($m <= 12) && ($y > 1902) && ($y <= 2037)) {
						$_SESSION["calendar_month"] = $m;
						$_SESSION["calendar_year"]  = $y;
					}
					$this->show_month($_SESSION["calendar_month"], $_SESSION["calendar_year"]);
				} else {
					/* Show appointment
					 */
					$this->show_appointment($this->page->parameters[0]);
				}
			} else {
				/* Show month
				 */
				$this->show_month($_SESSION["calendar_month"], $_SESSION["calendar_year"]);
			}
		}
	}
?>
