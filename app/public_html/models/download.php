<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class download_model extends Banshee\model {
		public function get_mimetype($file) {
			$default = "application/x-binary";

			if (file_exists("/etc/mime.types") == false) {
				return $default;
			}

			$info = pathinfo($file);
			if (isset($info["extension"]) == false) {
				return $default;
			}

			foreach (file("/etc/mime.types") as $line) {
				$line = trim($line);
				if (($line == "") || (substr($line, 0, 1) == "#")) {
					continue;
				}

				$line = preg_replace('/\s+/', ' ', $line);
				$extensions = explode(" ", $line);
				$mimetype = array_shift($extensions);

				if (in_array($info["extension"], $extensions)) {
					return $mimetype;
				}
			}

			return $default;
		}
	}
?>
