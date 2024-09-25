<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee\Database;

	class MySQL_PDO_connection extends PDO_connection {
		protected $type = "mysql";
		protected $id_delim = "`";
		protected $options = array(
			\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			\PDO::ATTR_EMULATE_PREPARES         => true);

		public function __construct() {
			$args = func_get_args();
			call_user_func_array(array(parent::class, "__construct"), $args);
		}
	}
?>
