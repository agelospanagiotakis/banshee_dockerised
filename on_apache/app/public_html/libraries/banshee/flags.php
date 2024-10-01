<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	class flags {
		private $flags = array();

		/* Constructor
		 *
		 * INPUT:  object database, object user, object page
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($db, $user, $page) {
			$query = "select * from flags f, user_role r ".
			         "where f.role_id=r.role_id and r.user_id=%d and f.module=%s";
			if (($flags = $db->execute($query, $user->id, $page->module)) === false) {
				return;
			}

			foreach ($flags as $flag) {
				array_push($this->flags, $flag["flag"]);
			}
		}

		/* Magic method get
		 *
		 * INPUT:  string key
		 * OUTPUT: boolean key exists as flag
		 * ERROR:  -
		 */
		public function __get($key) {
			return in_array($key, $this->flags);
		}
	}
?>
