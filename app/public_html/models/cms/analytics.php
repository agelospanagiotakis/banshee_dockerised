<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_analytics_model extends Banshee\model {
		private function arrange_data($data) {
			if ($data === false) {
				return false;
			}

			$now = time();

			$result = array();
			for ($i = ANALYTICS_DAYS - 1; $i >= 0; $i--) {
				$day = date("Y-m-d", strtotime("now - ".$i." days"));

				$count = 0;
				foreach ($data as $item) {
					if ($item["date"] == $day) {
						$count = $item["count"];
						break;
					}
				}

				$result[$day] = array(
					"date"  => $day,
					"count" => $count);
			}

			return $result;
		}

		public function get_visits($limit) {
			return $this->get_errors($limit, 0);
		}

		public function get_errors($limit, $error) {
			$query = "select date, count from log_visits where error=%d order by date desc limit %d";

			$result = $this->db->execute($query, $error, $limit);
			return $this->arrange_data($result);
		}

		public function get_page_views($limit) {
			$query = "select date, sum(%S) as %S from log_page_views group by date order by date desc limit %d";

			$result = $this->db->execute($query, "count", "count", $limit);
			return $this->arrange_data($result);
		}

		public function max_value($items, $key) {
			$max = 0;
			foreach ($items as $item) {
				if ($item[$key] > $max) {
					$max = $item[$key];
				}
			}

			return $max;
		}

		public function pad_data($data, $days) {
			if (($left = $days - count($data)) <= 0) {
				return;
			}

			for ($i = $days - $left; $i < $days; $i++) {
				$new = array(
					"date"  => date("Y-m-d", strtotime("-".$i." days")),
					"count" => 0);
				array_unshift($data, $new);
			}

			return $data;
		}

		public function get_top_pages($limit, $day = null) {
			$query = "select page, sum(count) as count from log_page_views";
			$args = array();
			if ($day !== null) {
				$query .= " where date=%s";
				array_push($args, $day);
			} else {
				$query .= " where date>%s";
				array_push($args, date("Y-m-d", strtotime("-".ANALYTICS_DAYS." days")));
			}
			$query .= " group by page order by count desc limit %d";
			array_push($args, $limit);

			return $this->db->execute($query, $args);
		}

		public function get_referers($day = null) {
			$query = "select hostname, sum(count) as count from log_referers where";
			$args = array();
			if ($day !== null) {
				$query .= " date=%s";
				array_push($args, $day);
			} else {
				$query .= " date>%s";
				array_push($args, date("Y-m-d", strtotime("-".ANALYTICS_DAYS." days")));
			}
			$query .= " group by hostname order by count desc";

			if (($hosts = $this->db->execute($query, $args)) === false) {
				return false;
			}

			$referers = array();

			$query = "select url, sum(count) as count from log_referers where";
			if ($day !== null) {
				$query .= " date=%s";
			} else {
				$query .= " date>%s";
			}
			$query .= " and hostname=%s group by url order by count desc";
			foreach ($hosts as $host) {
				if (($result = $this->db->execute($query, $args, $host["hostname"])) === false) {
					return false;
				}
				$referers[$host["hostname"]] = $result;
			}

			return $referers;
		}

		public function delete_referers($referers) {
			if (is_array($referers["hostname"]) == false) {
				return true;
			} else if (count($referers["hostname"]) == 0) {
				return true;
			}

			$queries = array();
			foreach ($referers["hostname"] as $hostname) {
				array_push($queries, array("delete from log_referers where hostname=%s", $hostname));
			}

			return $this->db->transaction($queries) !== false;
		}
	}
?>
