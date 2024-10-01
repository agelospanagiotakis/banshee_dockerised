<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	abstract class api_controller extends controller {
		protected $target = null;

		/* Set error code
		 *
		 * INPUT:  int error code
		 * OUTPUT: -
		 * ERROR:  -
		 */
		protected function set_error($code) {
			if ($code >= 400) {
				$this->view->add_tag("error", $code);
			}
			$this->page->set_http_code($code);
		}

		/* Default execute function
		 *
		 * INPUT:  -
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function execute() {
			if ($this->page->ajax_request == false) {
				$this->view->disable();
				return;
			}

			$parameters = $this->page->parameters;
			array_unshift($parameters, strtolower($_SERVER["REQUEST_METHOD"]));
			$target = array();

			/* Scan for function
			 */
			while (count($parameters) > 0) {
				$function = implode("_", $parameters);

				if (method_exists($this, $function)) {
					/* Set filename
					 */
					if (count($target) > 0) {
						$this->target = urldecode(implode("/", $target));
					}

					/* Get POST content
					 */
					if (($_SERVER["REQUEST_METHOD"] == "POST") && (($_SERVER["HTTP_CONTENT_TYPE"] ?? null) == "application/octet-stream")) {
						$_POST = file_get_contents("php://input");
					}

					/* Execute requested function
					 */
					if (call_user_func(array($this, $function)) === false) {
						$this->set_error(500);
					}

					return;
				}

				$parameter = array_pop($parameters);
				array_unshift($target, $parameter);
			}

			/* Return error
			 */
			$methods = array_diff(array("GET", "POST", "PUT", "DELETE"), array($_SERVER["REQUEST_METHOD"]));
			$allowed = array();
			foreach ($methods as $method) {
				if (method_exists($this, strtolower($method))) {
					array_push($allowed, $method);
				}
			}

			if (count($allowed) == 0) {
				$this->set_error(404);
			} else {
				$this->set_error(405);
				header("Allowed: ".implode(", ", $allowed));
			}
		}
	}
?>
