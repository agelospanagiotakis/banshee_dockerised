<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	class secure_cookie {
		private $settings = null;
		private $expire = null;

		/* Constructor
		 *
		 * INPUT:  object database
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($settings) {
			$this->settings = $settings;
			$this->expire = time() + 30 * DAY;
		}

		/* Set setting
		 *
		 * INPUT:  string key, mixed value
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __set($key, $value) {
			if ($value !== null) {
				$value = json_encode($value);

				$crypto = new Protocol\AES256($this->settings->secret_website_code);
				if (($value = $crypto->encrypt($value)) === false) {
					return;
				}

				$_COOKIE[$key] = $value;
				setcookie($key, $value, $this->expire, "/", "", is_true(ENFORCE_HTTPS), true);
			} else {
				unset($_COOKIE[$key]);
				setcookie($key, "", 1);
			}
		}

		/* Magic method get
		 *
		 * INPUT:  string key
		 * OUTPUT: mixed value
		 * ERROR:  null
		 */
		public function __get($key) {
			if (isset($_COOKIE[$key]) == false) {
				return null;
			}

			if ($this->settings->secret_website_code == null) {
				return null;
			}

			$crypto = new Protocol\AES256($this->settings->secret_website_code);
			if (($value = $crypto->decrypt($_COOKIE[$key])) === false) {
				return null;
			}

			return json_decode($value, true);
		}

		/* Set cookie expire time
		 *
		 * INPUT:  integer timeout
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function set_expire_time($time) {
			$this->expire = time() + $time;
		}
	}
?>
