<?php
	class demos_splitform_model extends Banshee\splitform_model {
		protected $forms = array(
			"one"   => array("name", "number"),
			"two"   => array("title", "content"),
			"three" => array("remark"));

		public function validate_two($data) {
			$result = true;

			foreach ($this->forms[$this->current_form] as $element) {
				if (trim($data[$element]) == "") {
					$this->view->add_message("The ".$element." cannot be empty.");
					$result = false;
				}
			}

			return $result;
		}

		public function process_form_data($data) {
			return true;
		}
	}
?>
