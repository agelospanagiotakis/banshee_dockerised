<?php
	require_once '../libraries/composer/cleantalk/php-antispam/cleantalk-antispam.php';
	use Cleantalk\CleantalkAntispam;

	
	class demos_cleantalk_controller extends Banshee\controller {
		public function execute() {
			$this->view->title = "Cleantalk demo";
			// read clentalk value from config cleantalk.conf
			$CLEATALK_KEY = "";
			$config_vals = get_columns(config_file("cleantalk"));
			foreach ($config_vals as $value) {
				list($key, $value) = explode("=", $value);
				if ($key == 'CLEATALK_KEY') {
					$CLEATALK_KEY = $value;
				}
			}
			// print_r($vals);
			// $CLEATALK_KEY = $vals[0]['CLEATALK_KEY'];
			$code = "" ;
			if (isset($_POST["code"])) {
				$code  = $_POST["code"];
			}
			$cleantalk_antispam = new CleantalkAntispam($CLEATALK_KEY, $code);
			$api_result = $cleantalk_antispam->handle();
			$anti_spam_front_end = $cleantalk_antispam->frontendScript();
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				// $valid = Banshee\captcha::valid_code();
				$valid =  $api_result->allow == 1;
				// var_dump($api_result);
				// die;
			// print("from demos_cleantalk_controller \n" . $anti_spam_front_end);
			$this->view->add_message("Valid code: %s.", show_boolean($valid));
			}
		}
	}

	function get_columns($pages) {
		$columns = array();

		foreach ($pages as $page) {
			$page = str_replace("*/", "", $page);
			array_push($columns, $page);
		}

		return array_unique($columns);
	}


?>
