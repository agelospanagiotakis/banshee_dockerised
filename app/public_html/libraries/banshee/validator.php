<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	class validator {
		private $view = null;
		private $messages = array(
			"boolean"   => "The field [label] should contain a boolean.",
			"charset"   => "The field [label] can only contain one of these characters: [charset].",
			"email"     => "The field [label] contains an invalid e-mail address.",
			"enum"      => "The field [label] does not contain one of the following values: [values].",
			"integer"   => "The field [label] should contain an integer.",
			"intmin"    => "The value of [label] should be at least [min].",
			"intmax"    => "The value of [label] should be at most [max].",
			"pattern"   => "The field [label] doesn't match with the required pattern [pattern].",
			"required"  => "The field [label] cannot be empty.",
			"timestamp" => "The field [label] contains an invalid timestamp.",
			"minlen"    => "The length of [label] must be at least [minlen].",
			"maxlen"    => "The length of [label] must not exceed [maxlen].");

		/* Constructor
		 *
		 * INPUT:  object view
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($view) {
			$this->view = $view;
		}

		/* Add validation feedback to output
		 *
		 * INPUT:  int message index, array message replacements
		 * OUTPUT: -
		 * ERROR:  -
		 */
		private function add_message($msg_idx, $replacements) {
			if (isset($replacements["message"])) {
				$message = $replacements["message"];
			} else {
				$message = $this->messages[$msg_idx];
				foreach ($replacements as $from => $to) {
					if (is_array($to)) {
						$to = implode(", ", $to);
					} else if (is_string($to) == false) {
						$to = (string)$to;
					}

					$message = str_replace("[".$from."]", $to, $message);
				}
			}

			$this->view->add_message($message);
		}

		/* Start validation process
		 *
		 * INPUT:  array pattern to validate POST data
		 * OUTPUT: boolean validation okay
		 * ERROR:  -
		 */
		public function execute($pattern) {
			$result = true;

			foreach ($pattern as $name => $rule) {
				if (isset($rule["label"]) == false) {
					$rule["label"] = $name;
				}

				if ($rule["required"] === true) {
					if (trim($_POST[$name]) == "") {
						$this->add_message("required", $rule);
						$result = false;
						continue;
					}
				}

				switch ($rule["type"]) {
					case "boolean":
						if (($_POST[$name] != null) && ($_POST[$name] != "On")) {
							$this->add_message("boolean", $rule);
							$result = false;
						}
						break;
					case "email":
						if ($_POST[$name] != "") {
							if (valid_email($_POST[$name]) == false) {
								$this->add_message("email", $rule);
								$result = false;
							}
						}
						break;
					case "enum":
						if ($_POST[$name] != "") {
							if (in_array($_POST[$name], $rule["values"]) == false) {
								$this->add_message("enum", $rule);
								$result = false;
							}
						}
						break;
					case "integer":
						if (valid_input($_POST[$name], VALIDATE_NUMBERS) == false) {
							$this->add_message("integer", $rule);
							$result = false;
						} else {
							if (isset($rule["min"])) {
								if ($_POST[$name] < $rule["min"]) {
									$this->add_message("intmin", $rule);
									$result = false;
								}
							}

							if (isset($rule["max"])) {
								if ($_POST[$name] > $rule["max"]) {
									$this->add_message("intmax", $rule);
									$result = false;
								}
							}
						}
						break;
					case "string":
						if (isset($rule["minlen"])) {
							if (strlen($_POST[$name]) < $rule["minlen"]) {
								$this->add_message("minlen", $rule);
								$result = false;
							}
						}

						if (isset($rule["maxlen"])) {
							if (strlen($_POST[$name]) > $rule["maxlen"]) {
								$this->add_message("maxlen", $rule);
								$result = false;
							}
						}

						if (isset($rule["charset"])) {
							if (valid_input($_POST[$name], $rule["charset"]) == false) {
								$this->add_message("charset", $rule);
								$result = false;
							}
						}

						if (isset($rule["pattern"])) {
							if (preg_match("/".$rule["pattern"]."/", $_POST[$name]) == false) {
								$this->add_message("pattern", $rule);
								$result = false;
							}
						}
						break;
					case "timestamp":
						if ($_POST[$name] != "") {
							if (valid_timestamp($_POST[$name]) == false) {
								$this->add_message("timestamp", $rule);
								$result = false;
							}
						}
						break;
					default:
						$this->view->add_message("No or invalid type set for ".$rule["label"].".");
				}
			}

			return $result;
		}
	}
?>
