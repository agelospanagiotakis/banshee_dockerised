<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	define("BANSHEE_VERSION", "8.0");
	define("ADMIN_ROLE_ID", 1);
	define("USER_ROLE_ID", 2);
	define("YES", 1);
	define("NO", 0);
	define("USER_STATUS_DISABLED", 0);
	define("USER_STATUS_CHANGEPWD", 1);
	define("USER_STATUS_ACTIVE", 2);
	define("PASSWORD_ALGORITHM", PASSWORD_ARGON2I);
	define("PASSWORD_MIN_LENGTH", 8);
	define("PASSWORD_MAX_LENGTH", 1000);
	define("ONE_TIME_KEY_SIZE", 32);
	define("URL_EXTENSION_MAX_LENGTH", 4);
	define("MAIL_NEW",                 0);
	define("MAIL_READ",                1);
	define("MAIL_DELETED_BY_SENDER",   2);
	define("MAIL_DELETED_BY_RECEIVER", 4);
	define("MAIL_ARCHIVED",            8);
	define("EURO", html_entity_decode("&euro;"));
	define("HOUR", 3600);
	define("DAY", 86400);
	define("KILOBYTE", 1024);
	define("MEGABYTE", KILOBYTE * 1024);
	define("GIGABYTE", MEGABYTE * 1024);
	define("ANALYTICS_DAYS", 60);
	define("EVENT_FAILED_LOGIN", 1);
	define("EVENT_EXPLOIT_ATTEMPT", 2);
	define("PAGE_MODULE", "banshee/page");
	define("FORM_MODULE", "banshee/form");
	define("ERROR_MODULE", "banshee/error");
	define("LOGIN_MODULE", "banshee/login");
	define("LOGOUT_MODULE", "logout");
	define("ACCOUNT_MODULE", "account");
	define("FPDF_FONTPATH", "../extra/fpdf_fonts/");
	define("K_PATH_FONTS", "../extra/tcpdf_fonts/");
	define("PHOTO_PATH", "photos");
	define("FILES_PATH", "files");
	define("TLS_CERT_SERIAL_VAR", "TLS_CERT_SERIAL");

	/* Crypto settings
	 */
	define("ENCRYPT_DATA", false);
	define("RSA_KEY_SIZE", 4096);
	define("CRYPTO_KEY_SIZE", 32);

	/* Auto class loader
	 *
	 * INPUT:  string class name
	 * OUTPUT: -
	 * ERROR:  -
	 */
	function banshee_autoload($class_name) {
		$parts = explode("\\", $class_name);
		$class = array_pop($parts);

		if (strtolower($parts[0] ?? "") == "banshee") {
			/* Load Banshee library
			 */
			array_shift($parts);
			$class = strtolower($class);
			array_unshift($parts, "..");
			$path = __DIR__."/".strtolower(implode("/", $parts));

			$rename = array(
				"https"     => "http",
				"pop3s"     => "pop3");

			if (isset($rename[$class])) {
				$class = $rename[$class];
			}
		} else {
			/* Load third party library
			 */
			$path = __DIR__."/../..";
		}

		if (file_exists($file = $path."/".strtolower($class).".php")) {
			include_once $file;
		} else if (file_exists($file = $path."/".$class.".php")) {
			include_once $file;
		}
	}

	/* COnvert a parth to a class name
	 *
	 * INPUT:  string module, string type
	 * OUTPUT: string
	 * ERROR:  -
	 */
	function module_to_class($module, $type) {
		return strtr($module, "/-.", "___")."_".$type;
	}

	/* Convert mixed to boolean
	 *
	 * INPUT:  mixed
	 * OUTPUT: boolean
	 * ERROR:  -
	 */
	function is_true($bool) {
		if (is_string($bool)) {
			$bool = strtolower($bool);
		}

		return in_array($bool, array(true, YES, "1", "yes", "true", "on"), true);
	}

	/* Convert mixed to boolean
	 *
	 * INPUT:  mixed
	 * OUTPUT: boolean
	 * ERROR:  -
	 */
	function is_false($bool) {
		return (is_true($bool) === false);
	}

	/* Convert boolean to string
	 *
	 * INPUT:  boolean
	 * OUTPUT: string "yes"|"no"
	 * ERROR:  -
	 */
	function show_boolean($bool) {
		return (is_true($bool) ? "yes" : "no");
	}

	/* Convert empty string to null
	 *
	 * INPUT:  string
	 * OUTPUT: string|null
	 * ERROR:  -
	 */
	function null_if_empty($str) {
		if (is_string($str) == false) {
			return $str;
		}

		if (trim($str) == "") {
			$str = null;
		}

		return $str;
	}

	/* Convert a page path to a module path
	 *
	 * INPUT:  array / string page path
	 * OUTPUT: array / string module path
	 * ERROR:  -
	 */
	function page_to_module($page) {
		if (is_array($page) == false) {
			if (($pos = strrpos($page, ".")) !== false) {
				if ((strlen($page) - $pos - 1) <= URL_EXTENSION_MAX_LENGTH) {
					$page = substr($page, 0, $pos);
				}
			}
		} else foreach ($page as $i => $item) {
			$page[$i] = page_to_module($item);
		}

		return $page;
	}

	/* Convert a page path to a page type
	 *
	 * INPUT:  array / string page path
	 * OUTPUT: array / string page type
	 * ERROR:  -
	 */
	function page_to_type($page) {
		if (is_array($page) == false) {
			if (($pos = strrpos($page, ".")) === false) {
				$page = "";
			} else if ((strlen($page) - $pos - 1) > URL_EXTENSION_MAX_LENGTH) {
				$page = "";
			} else {
				$page = substr($page, $pos + 1);
			}
		} else foreach ($page as $i => $item) {
			$page[$i] = page_to_type($item);
		}

		return $page;
	}

	/* Check for module existence
	 *
	 * INPUT:  string module
	 * OUTPUT: bool module exists
	 * ERROR:  -
	 */
	function module_exists($module) {
		foreach (array("public", "private") as $type) {
			if (in_array($module, config_file($type."_modules"))) {
				return true;
			}
		}

		return false;
	}

	/* Check for library existence
	 *
	 * INPUT:  string library
	 * OUTPUT: bool library exists
	 * ERROR:  -
	 */
	function library_exists($library) {
		$library = str_replace("\\", "/", $library);

		return file_exists(__DIR__."/../../".$library.".php");
	}

	/* Check for table existence
	 *
	 * INPUT:  db database object, string table name
	 * OUTPUT: bool table exists
	 * ERROR:  -
	 */
	function table_exists($db, $table) {
		if (($result = $db->execute("show tables like %s", $table)) == false) {
			return false;
		}

		return count($result[0]) > 0;
	}

	/* Handle table sort
	 */
	function handle_table_sort($key, $columns, $default) {
		if (isset($_SESSION[$key]) == false) {
			$_SESSION[$key] = $default;
		}

		if (isset($_GET["order"]) == false) {
			return;
		}

		if (in_array($_GET["order"], $columns) == false) {
			return;
		}

		if (is_array($default) == false) {
			$_SESSION[$key] = $_GET["order"];
			return;
		}

		$max = count($default) - 1;
		for ($i = 0; $i < $max; $i++) {
			if ($_SESSION[$key][$i] == $_GET["order"]) {
				return;
			}
		}

		array_pop($_SESSION[$key]);
		array_unshift($_SESSION[$key], $_GET["order"]);
	}

	/* Log event for analytics
	 *
	 * INPUT:  database object, HTTP status code / event code
	 * OUTPUT: false
	 * ERROR:  -
	 */
	function log_event($db, $error) {
		switch ($error) {
			case EVENT_FAILED_LOGIN:
				header("X-Hiawatha-Monitor: failed_login");
				break;
			case EVENT_EXPLOIT_ATTEMPT:
				header("X-Hiawatha-Monitor: exploit_attempt");
				break;
		}

		if (library_exists("banshee/analytics") == false) {
			return false;
		}

		$today = date("Y-m-d");

		$query = "update log_visits set count=count+1 where date=%s and error=%d";
		if (($result = $db->execute($query, $today, $error)) === false) {
			return;
		} else if ($result > 0) {
			return;
		}

		$data = array(
			"id"    => null,
			"date"  => $today,
			"count" => 1,
			"error" => $error);
		return $db->insert("log_visits", $data) !== false;
	}

	/* Log debug information
	 *
	 * INPUT:  string format[, mixed arg...]
	 * OUTPUT: -
	 * ERROR:  -
	 */
	function debug_log($info) {
		static $logfile = null;

		if ($logfile === null) {
			$logfile = new \Banshee\logfile("debug");
		}

		call_user_func_array(array($logfile, "add_entry"), func_get_args());
	}

	/* Flatten array to new array with depth 1
	 *
	 * INPUT:  array data
	 * OUTPUT: array data
	 * ERROR:  -
	 */
	function array_flatten($data) {
		$result = array();
		foreach ($data as $item) {
			if (is_array($item)) {
				$result = array_merge($result, array_flatten($item));
			} else {
				array_push($result, $item);
			}
		}

		return $result;
	}

	/* Localized date string
	 *
	 * INPUT:  string format[, integer timestamp]
	 * OUTPUT: string date
	 * ERROR:  -
	 */
	function date_string($format, $timestamp = null) {
		if ($timestamp === null) {
			$timestamp = time();
		}

		$days_of_week = config_array(DAYS_OF_WEEK);
		$months_of_year = config_array(MONTHS_OF_YEAR);

		$format = strtr($format, "lDFM", "#$%&");
		$result = date($format, $timestamp);

		$day = $days_of_week[(int)date("N", $timestamp) - 1];
		$result = str_replace("#", $day, $result);

		$day = substr($days_of_week[(int)date("N", $timestamp) - 1], 0, 3);
		$result = str_replace("$", $day, $result);

		$month = $months_of_year[(int)date("n", $timestamp) - 1];
		$result = str_replace("%", $month, $result);

		$month = substr($months_of_year[(int)date("n", $timestamp) - 1], 0, 3);
		$result = str_replace("&", $month, $result);

		return $result;
	}

	/* Load configuration file
	 *
	 * INPUT:  string configuration file[, bool remove comments]
	 * OUTPUT: array( key => value[, ...] )
	 * ERROR:  -
	 */
	function config_file($config_file, $remove_comments = true) {
		static $cache = array();

		if (isset($cache[$config_file])) {
			return $cache[$config_file];
		}

		$first_char = substr($config_file, 0, 1);
		if (($first_char != "/") && ($first_char != ".")) {
			$config_file = __DIR__."/../../../settings/".$config_file.".conf";
		}
		if (file_exists($config_file) == false) {
			return array();
		}

		$config = array();
		foreach (file($config_file) as $line) {
			if ($remove_comments) {
				$line = trim(preg_replace("/(^|\s)#.*/", "", $line));
			}
			$line = rtrim($line);

			if ($line === "") {
				continue;
			}

			if (($prev = count($config) - 1) == -1) {
				array_push($config, $line);
			} else if (substr($config[$prev], -1) == "\\") {
				$config[$prev] = rtrim(substr($config[$prev], 0, strlen($config[$prev]) - 1)) . ltrim($line);
			} else {
				array_push($config, $line);
			}
		}

		$cache[$config_file] = $config;

		return $config;
	}

	/* Convert configuration line to array
	 *
	 * INPUT:  string config line[, bool look for key-value
	 * OUTPUT: array config line
	 * ERROR:  -
	 */
	function config_array($line, $key_value = true) {
		if (trim($line) == "") {
			return array();
		}

		$items = explode("|", $line);

		if ($key_value == false) {
			return $items;
		}

		$result = array();
		foreach ($items as $item) {
			$parts = explode(":", $item, 2);
			if (count($parts) == 1) {
				array_push($result, $item);
			} else {
				$result[$parts[0]] = $parts[1];
			}
		}

		return $result;
	}


	/* Hide the reverse proxy.
	 *
	 * INPUT:  -
	 * OUTPUT: -
	 * ERROR:  -
	 */
	function hide_reverse_proxy() {
		if (REVERSE_PROXY == null) {
			return;
		}

		$reverse_proxy = config_array(REVERSE_PROXY);
		if (in_array($_SERVER["REMOTE_ADDR"], $reverse_proxy) == false) {
			return;
		}

		if (isset($_SERVER["X_FORWARDED_FOR"])) {
			$addresses = explode(",", $_SERVER["X_FORWARDED_FOR"]);
			$_SERVER["REMOTE_ADDR"] = trim($addresses[0]);
		} else if (isset($_SERVER["HTTP_FORWARDED"])) {
			$parts = explode(";", $_SERVER["HTTP_FORWARDED"]);
			foreach ($parts as $part) {
				list($key, $value) = explode("=", $part);
				if (trim($key) == "for") {
					list($value) = explode(",", $value);
					$_SERVER["REMOTE_ADDR"] = trim($value);
					break;
				}
			}
		}
	}

	/* Website configuration
	 */
	if (isset($_ENV["banshee_config_file"])) {
		$config_file = $_ENV["banshee_config_file"];
	} else {
		$config_file = "banshee";
	}

	foreach (config_file($config_file) as $line) {
		list($key, $value) = explode("=", chop($line), 2);
		define(trim($key), trim($value));
	}

	$extra_config = config_array(EXTRA_SETTINGS);
	foreach ($extra_config as $config_file) {
		foreach (config_file($config_file) as $line) {
			list($key, $value) = explode("=", chop($line), 2);
			define(trim($key), trim($value));
		}
	}

	/* PHP warning prevention
	 */
	if (isset($_SERVER["REQUEST_METHOD"]) == false) {
		$_SERVER["REQUEST_METHOD"] = null;
	} else if (($_SERVER["REQUEST_METHOD"] == "POST") && (isset($_POST["submit_button"]) == false)) {
		$_POST["submit_button"] = null;
	}

	/* Autoloaders
	 */
	$composer = __DIR__."/../../composer/autoload.php";
	if (file_exists($composer)) {
		include($composer);
	}
	spl_autoload_register("banshee_autoload", true, true);

	/* Check PHP version and settings
	 */
	if (version_compare(PHP_VERSION, "7.2") < 0) {
		exit("This system uses an unsupported PHP version. Use at least PHP 7.2.");
	}
	ini_set("zlib.output_compression", "Off");

	hide_reverse_proxy();
?>
