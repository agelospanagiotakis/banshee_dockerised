<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee\Protocol;

	class iCalendar {
		private $title = null;
		private $prod_id = null;
		private $items = array();

		public function __construct($title, $prod_id) {
			$this->title = $title;
			$this->prod_id = $prod_id;
		}

		public function add_item($summary, $description, $begin, $end = null) {
			$item = array(
				"summary"     => $summary,
				"description" => $description,
				"begin"       => $begin,
				"end"         => $end);

			array_push($this->items, $item);
		}

		private function explode_timestamp($timestamp) {
			$timestamp = date("d m Y H i s", $timestamp);

			return explode(" ", $timestamp);
		}

		private function secure_string($str) {
			if ($str == null) {
				return "";
			}

			$str = strip_tags($str);
			$str = str_replace("\r", "", $str);
			$str = str_replace("\n", " ", $str);

			return $str;
		}

		public function generate() {
			$timezone = date("e");
			$result =
				"BEGIN:VCALENDAR\r\n".
				"VERSION:2.0\r\n".
				"PRODID:-//".$this->prod_id."\r\n".
				"X-WR-CALNAME;VALUE=TEXT:".$this->title."\r\n";

			foreach ($this->items as $item) {
				list($day, $month, $year, $hour, $minute, $second) = $this->explode_timestamp($item["begin"]);
				list($now_day, $now_month, $now_year) = explode(" ", date("d m Y"));

				$result .= "BEGIN:VEVENT\r\n";
				$result .= sprintf("UID:%s\r\n", hash("sha256", json_encode($item)));
				$result .= sprintf("DTSTAMP;TZID=%s:%s%s%sT000000\r\n", $timezone, $now_year, $now_month, $now_day);
				$result .= sprintf("SUMMARY:%s\r\n", $this->secure_string($item["summary"]));
				$result .= sprintf("DESCRIPTION:%s\r\n", $this->secure_string($item["description"]));

				$format = "DT%s;TZID=/mozilla.org/20050126_1/%s:%s%s%sT%s%s%s\r\n";

				$result .= sprintf($format, "START", $timezone, $year, $month, $day, $hour, $minute, $second);

				if ($item["end"] != null) {
					list($day, $month, $year, $hour, $minute, $second) = $this->explode_timestamp($item["end"]);
					$result .= sprintf($format, "END", $timezone, $year, $month, $day, $hour, $minute, $second);
				}

				$result .= "END:VEVENT\r\n";
			}

			$timezone_diff = date("O");

			$result .=
				"BEGIN:VTIMEZONE\r\n".
				"TZID:/mozilla.org/20050126_1/".$timezone."\r\n".
				"X-LIC-LOCATION:".$timezone."\r\n".
				"BEGIN:DAYLIGHT\r\n".
				"TZOFFSETFROM:+0100\r\n".
				"TZOFFSETTO:+0200\r\n".
				"TZNAME:CEST\r\n".
				"DTSTART:19700329T020000\r\n".
				"RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=3\r\n".
				"END:DAYLIGHT\r\n".
				"BEGIN:STANDARD\r\n".
				"TZOFFSETFROM:+0200\r\n".
				"TZOFFSETTO:+0100\r\n".
				"TZNAME:CET\r\n".
				"DTSTART:19701025T030000\r\n".
				"RRULE:FREQ=YEARLY;INTERVAL=1;BYDAY=-1SU;BYMONTH=1\r\n".
				"END:STANDARD\r\n".
				"END:VTIMEZONE\r\n".
				"END:VCALENDAR\r\n";

			return $result;
		}

		public function to_view($view) {
			$view->disable();

			if (strstr($_SERVER["HTTP_USER_AGENT"], "Firefox") !== false) {
				header("Content-Type: text/plain");
			} else {
				header("Content-Type: text/calendar");
			}

			print $this->generate();
		}
	}
?>
