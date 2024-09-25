<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee\Protocol;

	class NMD {
		const SERVER = "www.notifymydevice.com";

		private $server = null;
		private $api_key = null;

		/* Constructor
		 *
		 * INPUT:  string API key
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($api_key) {
			$this->server = new HTTPS(self::SERVER);
			$this->api_key = $api_key;
		}

		/* Send push notification
		 *
		 * INPUT:  string title, string text
		 * OUTPUT: true sending successful
		 * ERROR:  false sending failed
		 */
		public function send_notification($title, $text) {
			$data = array(
				"ApiKey"    => $this->api_key,
				"PushTitle" => $title,
				"PushText"  => $text);

			if (($result = $this->server->POST("/push", $data)) === false) {
				return null;
			} else if ($result["status"] != 200) {
				return false;
			}

			return true;
		}
	}
?>
