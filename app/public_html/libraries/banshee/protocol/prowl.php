<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee\Protocol;

	class prowl {
		const SERVER = "api.prowlapp.com";

		private $server = null;
		private $application = null;
		private $api_keys = null;
		private $provider_key = null;

		/* Constructor
		 *
		 * INPUT:  string application, array/string API keys[, string provider key]
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($application, $api_keys, $provider_key = null) {
			$this->server = new HTTPS(self::SERVER);
			$this->application = $this->truncate($application, 256);
			if (is_array($api_keys) == false) {
				$this->api_keys = array($api_keys);
			} else {
				$this->api_keys = $api_keys;
			}
			$this->provider_key = $provider_key;
		}

		/* Truncate text
		 *
		 * INPUT:  string text, integer maximum text length
		 * OUTPUT: string truncated text
		 * ERROR:  -
		 */
		private function truncate($text, $size) {
			if (strlen($text) > $size) {
				$text = substr($text, 0, $size - 3)."...";
			}

			return $text;
		}

		/* Send push notification
		 *
		 * INPUT:  string event, string description[, int priority[, string url]]
		 * OUTPUT: true sending successful
		 * ERROR:  false sending failed
		 */
		public function send_notification($event, $description, $priority = 0, $url = null) {
			if ((is_int($priority) == false) || ($priority < -2) || ($priority > 2)) {
				return false;
			}

			$data = array(
				"apikey"      => implode(",", $this->api_keys),
				"application" => $this->application,
				"event"       => $this->truncate($event, 1024),
				"description" => $this->truncate($description, 10000));

			if ($priority != 0) {
				$data["priority"] = $priority;
			}

			if ($url !== null) {
				$data["url"] = $this->truncate($url, 512);
			}

			if ($this->provider_key !== null) {
				$data["provider_key"] = $this->provider_key;
			}

			if (($result = $this->server->POST("/publicapi/add", $data)) === false) {
				return null;
			} else if ($result["status"] != 200) {
				return false;
			}

			return true;
		}

		/* Verify API key
		 *
		 * INPUT:  string API key
		 * OUTPUT: bool valid key
		 * ERROR:  null validation error
		 */
		public function valid_key($api_key) {
			$params = "apikey=".$api_key;

			if ($this->provider_key !== null) {
				$params .= "&providerkey=".$this->provider_key;
			}

			if (($result = $this->server->GET("/publicapi/verify?".$params)) === false) {
				return null;
			} else if ($result["status"] != 200) {
				return false;
			}

			return true;
		}
	}
?>
