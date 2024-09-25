<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee\Database;

	class LDAP_connection {
		private $hostname = null;
		private $basedn = null;
		private $username = null;
		private $password = null;
		private $link = false;

		public function __construct($hostname, $basedn, $username, $password) {
			$this->hostname = $hostname;
			$this->basedn = $basedn;
			$this->username = $username;
			$this->password = $password;

			if (($this->link = ldap_connect($hostname)) === false) {
				return false;
			}

			if (ldap_bind($this->link, $username, $password) == false) {
				return false;
			}

			return true;
		}

		private function get_user($username) {
			static $users = array();

			if (isset($users[$username])) {
				return $users[$username];
			}

			if (($resource = ldap_search($this->link, $this->basedn, "userprincipalname=".$username)) === false) {
				return false;
			}

			if (($result = ldap_get_entries($this->link, $resource)) === false) {
				return false;
			} else if ($result["count"] == 0) {
				return false;
			}

			$users[$username] = $result[0];

			return $result[0];
		}

		public function authenticate($username, $password) {
			if ($password == "") {
				return false;
			}

			if (($user = $this->get_user($username)) === false) {
				return false;
			}

			return ldap_bind($this->link, $user["dn"], $password);
		}
	}
?>
