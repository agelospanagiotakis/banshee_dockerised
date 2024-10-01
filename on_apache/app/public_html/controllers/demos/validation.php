<?php
	class demos_validation_controller extends Banshee\controller {
		private $pattern = array(
			"string" => array(
				"type"     => "string",
				"required" => true,
				"charset"  => VALIDATE_CAPITALS,
				"pattern"  => "^A.*",
				"minlen"   => 10,
				"maxlen"   => 15),
			"number" => array(
				"type"     => "integer",
				"required" => true),
			"enum" => array(
				"type"     => "enum",
				"required" => false,
				"values"   => array("one", "two", "three")));

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				$validator = new \Banshee\validator($this->view);

				if ($validator->execute($this->pattern)) {
					$this->view->add_system_message("Data validation okay.");
				}
			}

			$this->view->record($_POST);
		}
	}
?>
