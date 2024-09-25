<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	class graph {
		private static $instances = 0;
		private $graph_id = null;
		private $view = null;
		private $height = 150;
		private $width = 500;
		private $title = null;
		private $bars = array();
		private $links = array();

		/* Constructor
		 *
		 * INPUT:  -
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($view) {
			$this->view = $view;
			$this->graph_id = ++self::$instances;
		}

		/* Magic method set
		 *
		 * INPUT:  string key, mixed value
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __set($key, $value) {
			switch ($key) {
				case "height": $this->height = $value; break;
				case "title": $this->title = $value; break;
				case "width": $this->width = $value; break;
			}
		}

		/* Add a bar to the graph
		 *
		 * INPUT:  mixed x, integer y[, string link]
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function add_bar($x, $y, $link = null) {
			$this->bars[$x] = $y;
			if ($link != null) {
				$this->links[$x] = $link;
			}
		}

		/* Convert a big number to a more readable one
		 *
		 * INPUT:  integer number
		 * OUTPUT: string number
		 * ERROR:  -
		 */
		static public function readable_number($number) {
			if ($number > 1000000000) {
				return sprintf("%0.1f G", $number / 1000000000);
			} else if ($number > 1000000) {
				return sprintf("%0.1f M", $number / 1000000);
			} else if ($number > 1000) {
				return sprintf("%0.1f k", $number / 1000);
			}

			return $number;
		}

		/* Graph to output
		 *
		 * INPUT:  -
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function add_to_view() {
			if (($bar_count = count($this->bars)) == 0) {
				return;
			}

			$max_y = 0;
			foreach ($this->bars as $y) {
				if ($y > $max_y) {
					$max_y = $y;
				}
			}

			$this->view->add_css("banshee/graph.css");
			$this->view->add_javascript("banshee/graph.js");

			$params = array(
				"id"         => $this->graph_id,
				"height"     => $this->height,
				"width"      => $this->width,
				"max_y"      => $this->readable_number($max_y),
				"bar_width"  => sprintf("%0.2f", $this->width / $bar_count));
			$this->view->open_tag("graph", $params);
			if ($this->title !== NULL) {
				$this->view->add_tag("title", $this->title);
			}
			foreach ($this->bars as $x => $y) {
				$bar_y = ($max_y == 0) ? 0 : sprintf("%0.2f", $y * $this->height / $max_y);
				$params = array(
					"text"  => $x,
					"value" => $this->readable_number($y));
				if (isset($this->links[$x])) {
					$params["link"] = $this->links[$x];
				}
				$this->view->add_tag("bar", $bar_y, $params);
			}
			$this->view->close_tag();
		}
	}
?>
