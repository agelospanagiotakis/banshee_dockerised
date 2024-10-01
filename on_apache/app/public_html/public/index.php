<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	ob_start();
	require "../libraries/banshee/core/error.php";
	require "../libraries/banshee/core/banshee.php";
	require "../libraries/banshee/core/security.php";

	/* Create core objects
	 */
	$_database = new Database\MySQLi_connection(DB_HOSTNAME, DB_DATABASE, DB_USERNAME, DB_PASSWORD);
	$_settings = new Core\settings($_database);
	$_session  = new Core\session($_database, $_settings);
	$_user     = new Core\user($_database, $_settings, $_session);
	$_page     = new Core\page($_database, $_settings, $_user);
	$_view     = new Core\view($_database, $_settings, $_user, $_page);
	$_language = is_true(MULTILINGUAL) ? new Core\language($_database, $_page, $_view) : null;

	/* Web Analytics
	 */
	if (library_exists("banshee/analytics") && ($_user->is_admin == false)) {
		$analytics = new analytics($_database, $_page);
		$analytics->execute();
	}

	/* User switch warning
	 */
	if (isset($_SESSION["user_switch"])) {
		$real_user = $_database->entry("users", $_SESSION["user_switch"]);
		$_view->add_system_warning("User switch active! Switched from '%s' to '%s'.", $real_user["fullname"], $_user->fullname);
	}

	/* Include the model
	 */
	if (file_exists($file = "../models/".$_page->module.".php")) {
		include($file);

		/* Set output type for API modules
		 */
		$model_class = module_to_class($_page->module, "model");
		if (class_exists($model_class)) {
			if (is_subclass_of($model_class, "api_model")) {
				$_view->mode = API_OUTPUT_TYPE;
				$_view->add_layout_data = false;
			}
		}
	}

	/* Add layout data to output XML
	 */
	$_view->open_tag("output");

	if ($_view->add_layout_data) {
		$_view->open_tag("banshee");
		$_view->add_tag("version", BANSHEE_VERSION);
		if ($_user->logged_in) {
			$_view->add_tag("session_timeout", $_session->timeout);
		}
		$_view->close_tag();
		$_view->add_tag("hostname", $_SERVER["SERVER_NAME"]);

		$http_scheme = $_SERVER["HTTP_SCHEME"] ?? "http";

		/* Page information
		 */
		$_view->add_tag("page", $_page->page, array(
			"base"     => $http_scheme."://".$_SERVER["HTTP_HOST"],
			"url"      => $_page->url,
			"module"   => $_page->module,
			"type"     => $_page->type,
			"readonly" => show_boolean($_page->readonly)));

		/* User information
		 */
		if ($_user->logged_in) {
			$params = array("id" => $_user->id, "admin" => show_boolean($_user->is_admin));
			$_view->add_tag("user", $_user->fullname, $params);
		}

		/* Multilingual
		 */
		if ($_language !== null) {
			$_language->add_to_view();
		}

		/* Unsecured connection
		 */
		if ((($_SERVER["HTTPS"] ?? null) != "on") && ($http_scheme != "https")) {
			$pages = array(LOGIN_MODULE, "register", "password");
			if (in_array($_page->module, $pages) || (substr($_page->module, 0, 3) == "cms")) {
				$_view->add_system_warning("Warning, the connection you are using is not secure!");
			}
		}

		/* Main menu
		 */
		// var_dump("WEBSITE_ONLINE", WEBSITE_ONLINE);
		// var_dump("REMOTE_ADDR", $_SERVER["REMOTE_ADDR"]);
		if (is_true(WEBSITE_ONLINE) ) { //|| ($_SERVER["REMOTE_ADDR"] == WEBSITE_ONLINE)) {
			if ((substr($_page->url, 0, 4) == "/cms") || ($_view->layout == LAYOUT_CMS)) {
				/* CMS menu
				 */
				$_view->open_tag("menu");
				$_view->record(array("link" => "/", "text" => "Homepage"), "item");
				if (($_user->logged_in) && ($_page->page != LOGOUT_MODULE)) {
					$_view->record(array("link" => "/cms", "text" => "CMS"), "item");
					$_view->record(array("link" => "/".LOGOUT_MODULE, "text" => "Logout"), "item");
				}
				$_view->close_tag();
			} else if ($_user->logged_in || is_false(HIDE_MENU_FOR_VISITORS)) {
				/* Normal menu
				 */
				$menu = new menu($_database, $_page, $_view);
				if (is_true(MENU_PERSONALIZED) && $_user->logged_in) {
					$menu->set_user($_user);
				}
				$menu->set_depth(2);
				$menu->add_to_view();
			}
		}

		/* Add javascripts to output
		 */
		$_view->add_javascript("webui/jquery.js");
		$_view->add_javascript("webui/bootstrap.js");

		$_view->open_tag("content", array("mobile" => show_boolean($_view->mobile)));
	}

	/* Include the controller
	 */
	if (file_exists($file = "../controllers/".$_page->module.".php")) {
		include($file);

		$controller_class = module_to_class($_page->module, "controller");
		if (class_exists($controller_class) == false) {
			print "Controller class '".$controller_class."' does not exist.\n";
		} else if (is_subclass_of($controller_class, "Banshee\\controller") == false) {
			print "Controller class '".$controller_class."' does not extend Banshee's controller class.\n";
		} else {
			$_controller = new $controller_class($_database, $_settings, $_user, $_page, $_view, $_language);
			$_controller->execute();
			unset($_controller);

			if ($_view->disabled) {
				print ob_get_clean();
				exit;
			}

			while ($_view->depth > 2) {
				print "System error: controller didn't close an open tag.";
				$_view->close_tag();
			}
		}
	}

	if ($_view->add_layout_data) {
		/* Prepend stylesheets to output
		 */
		$_view->add_css($_page->module.".css", true);
		$_view->add_css("banshee/layout_".$_view->layout.".css", true);
		$_view->add_css("banshee/banshee.css", true);
		if (is_true(USE_CKEDITOR)) {
			$_view->add_css("banshee/ckstyles.css", true);
		}
		$_view->add_css("webui/bootstrap-theme.css", true);
		$_view->add_css("webui/bootstrap.css", true);

		$_view->close_tag();
	}

	/* Handle errors
	 */
	if (($errors = ob_get_contents()) != "") {
		$error_handler = new Core\website_error_handler($_view, $_settings, $_user);
		$error_handler->execute($errors);
	}
	ob_clean();

	/* Close output
	 */
	$_view->close_tag();

	/* Output content
	 */
	$view_result = $_view->generate();
	if ((($last_errors = ob_get_clean()) != "") && ($_page->module != "setup")) {
		$last_errors = "Fatal errors:\n".$last_errors;
		header_remove("Content-Encoding");
		header_remove("Content-Length");
		header("Content-Type: text/html");
		throw new \Exception($last_errors);
	} else {
		print $view_result;
	}
?>
