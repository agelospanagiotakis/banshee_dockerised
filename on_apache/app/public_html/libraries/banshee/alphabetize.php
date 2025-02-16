<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	class alphabetize {
		private $view = null;
		private $name = null;
		private $char = "0";

		/* Constructor
		 *
		 * INPUT:  object view, string name
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($view, $name) {
			$this->view = $view;
			$this->name = $name;

			/* Initialize session storage
			 */
			if (is_array($_SESSION["alphabetize"] ?? null) == false) {
				$_SESSION["alphabetize"] = array();
			}
			if (isset($_SESSION["alphabetize"][$name]) == false) {
				$_SESSION["alphabetize"][$name] = $this->char;
			}

			/* Set starting character
			 */
			$this->char = &$_SESSION["alphabetize"][$name];
			if (isset($_GET["char"])) {
				$this->char = $this->make_valid_char($_GET["char"]);
			}

			$this->view->add_css("banshee/alphabetize.css");
		}

		/* Magic method get
		 *
		 * INPUT:  string key
		 * OUTPUT: mixed value
		 * ERROR:  null
		 */
		public function __get($key) {
			switch ($key) {
				case "char": return $this->char;
			}

			return null;
		}

		/* Makes the start charater a valid one
		 *
		 * INPUT:  string starting character
		 * OUTPUT: string valid starting character
		 * ERROR:  -
		 */
		private function make_valid_char($char) {
			if ($char == "") {
				return "0";
			}

			$char = strtolower(substr($char, 0, 1));

			if ((ord($char) < ord("a")) || (ord($char) > ord("z"))) {
				return "0";
			}

			return $char;
		}

		/* Set active page to "0"
		 *
		 * INPUT:  -
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function reset() {
			$this->char = "0";
		}

		/* Generate XML for the browse links
		 *
		 * INPUT:  -
		 * OUTPUT: boolean xml generated
		 * ERROR:  -
		 */
		public function show_browse_links() {
			$this->view->open_tag("alphabetize", array("char" => $this->char));

			$this->view->add_tag("char", "#", array("link" => "0"));
			for ($c = ord("a"); $c <= ord("z"); $c++) {
				$this->view->add_tag("char", chr($c), array("link" => chr($c)));
			}

			$this->view->close_tag();
		}

		/* Returns content of table for current start character
		 *
		 * INPUT:  object database, string table name, string column name[, string column name for ordering]
		 * OUTPUT: array table content
		 * ERROR:  false
		 */
		public function get_items($db, $table, $column, $order = null) {
			$query = "select * from %S";
			$args = array($table);

			if ($this->char == "0") {
				$query .= " where ord(lower(substr(%S, 1, 1)))<ord(%s) or ord(lower(substr(%S, 1, 1)))>ord(%s)";
				array_push($args, $column, "a", $column, "z");
			} else {
				$query .= " where %S like %s";
				array_push($args, $column, $this->char."%");
			}

			if ($order != null) {
				$query .= " order by %S";
				array_push($args, $order);
			}

			return $db->execute($query, $args);
		}
	}
?>
