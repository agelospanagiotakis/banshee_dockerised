<?php
	class Spider {
		private $hostname = null;
		private $website = null;
		private $visited = array();
		private $term_width = 80;
		private $url_len = 0;

		/* Constructor
		 */
		public function __construct($url) {
			list($protocol,, $this->hostname, $start_path) = explode("/", $url, 4);

			if (gethostbyname($this->hostname) == false) {
				exit("Invalid hostname.\n");
				return;
			}

			switch ($protocol) {
				case "http:": $this->website = new \Banshee\Protocol\HTTP($this->hostname); break;
				case "https:": $this->website = new \Banshee\Protocol\HTTPS($this->hostname); break;
				default: exit("Invalid protocol.\n");
			}

			$view = explode(";", exec("stty -a | grep columns"));
			foreach ($view as $line) {
				list($key, $value) = explode(" ", ltrim($line), 2);
				if ($key == "columns") {
					$this->term_width = $value - 12;
					break;
				}
			}
		}

		/* Show HTTP fetch result
		 */
		private function show_result($result, $url, $parent) {
			$str = substr($url, 0, $this->term_width);
			printf(" - checking %s\r", str_pad($str, $this->url_len));
			$this->url_len = strlen($str);

			if ($result == false) {
				printf(" ! network error while fetching %s (via %s)\n", $url, $parent);
			}

			if (($result["status"] == 301) || ($result["status"] == 302)) {
				printf(" ! %d Moved: %s\n          to: %s (via %s)\n",
				       $result["status"], $url, $result["headers"]["location"], $parent);
			}

			if ($result["status"] == 404) {
				printf(" ! 404 Not Found: %s (via %s)\n", $url, $parent);
			}
		}

		/* Check full URL
		 */
		private function check_url($url, $parent) {
			$url = preg_replace('/#.*/', "", $url);

			list($protocol,, $hostname, $path) = explode("/", $url, 4);
			$path = "/".$path;

			if ($hostname == $this->hostname) {
				$this->check_path($path, $parent);
				return;
			}

			if (in_array($url, $this->visited)) {
				return;
			}
			array_push($this->visited, $url);

			switch ($protocol) {
				case "http:": $website = new \Banshee\Protocol\HTTP($hostname); break;
				case "https:": $website = new \Banshee\Protocol\HTTPS($hostname); break;
				default: exit("Invalid protocol.\n");
			}

			$result = $website->GET($path);
			$this->show_result($result, $url, $parent);
		}

		/* Check path within target website
		 */
		private function check_path($path, $parent) {
			$path = preg_replace('/#.*/', "", $path);

			if (in_array($path, $this->visited)) {
				return;
			}
			array_push($this->visited, $path);

			$result = $this->website->GET($path);
			$this->show_result($result, $path, $parent);

			if (substr($result["headers"]["content-type"], 0, 9) != "text/html") {
				return;
			}

			$pos = 0;
			while (($pos = strpos($result["body"], '="', $pos)) !== false) {
				$pos += 2;
				if (($end = strpos($result["body"], '"', $pos)) === false) {
					break;
				}

				$value = substr($result["body"], $pos, $end - $pos);

				if (substr($value, 0, 1) == "/") {
					$this->check_path($value, $path);
				} else if (substr($value, 0, 5) == "http:") {
					$this->check_url($value, $path);
				} else if (substr($value, 0, 6) == "https:") {
					$this->check_url($value, $path);
				}

				$pos = $end + 1;
			}
		}

		/* Crawl target website
		 */
		public function crawl() {
			if ($this->hostname === null) {
				return;
			}

			$this->check_path("/", "initial request");
			if ($this->url_len > 0) {
				print str_repeat(" ", $this->url_len + 12)."\r";
			}
		}
	}
?>
