<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	class DirectoryIndex {
		const kB = 1024;
		const MB = 1024 * self::kB;
		const GB = 1024 * self::MB;

		private $view = null;
		private $path = null;

		/* Constructor
		 *
		 * INPUT:  object xml
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($view) {
			$this->view = $view;
		}

		/* Structure
		 */
		public function get_structure($directory) {
			if (($dp = opendir($directory)) == false) {
				return false;
			}

			$structure = array();
			while (($file = readdir($dp)) != false) {
				if (substr($file, 0, 1) == ".") {
					continue;
				}
				$path = $directory."/".$file;
				if (is_dir($path)) {
					$structure[$file] = $this->get_structure($path);
				}
			}
			ksort($structure);

			closedir($dp);

			return $structure;
		}

		private function structure($directory, $depth) {
			foreach ($directory as $file => $subdir) {
				$type = $file == $this->path[$depth] ? "open" : "closed";
				$params = array(
					"name"  => $file,
					"depth" => $depth,
					"type"  => $type);
				$this->view->open_tag("structure", $params);
				$this->structure($subdir, $depth +1);
				$this->view->close_tag();
			}
		}

		private function count_files(&$directory) {
			$count = $directory["__files"];
			unset($directory["__files"]);

			foreach ($directory as $file => $subdir) {
				$count += $this->count_files($directory[$file]);
			}

			$directory["__files"] = $count;

			return $count;
		}

		public function show_structure($root) {
			if (($directory = $this->get_structure($root)) == false) {
				return false;
			}

			//$this->count_files($directory);

			$this->structure($directory, 0);
		}

		/* Show path breadcrumbs
		 */
		private function show_path($url) {
			$this->view->open_tag("path");
			$path = "/";
			$this->view->add_tag("dir", "Home", array("path" => $path));
			if ($url != "") {
				$dirs = explode("/", trim($url, "/"));
				foreach ($dirs as $dir) {
					$path .= $dir."/";
					$this->view->add_tag("dir", $dir, array("path" => $path));
				}
			}
			$this->view->close_tag();
		}

		/* Nice size
		 */
		private function nice_size($size) {
			if ($size > self::GB) {
				$size = round($size / self::GB, 1)." GB";
			} else if ($size > self::MB) {
				$size = round($size / self::MB, 1)." MB";
			} else if ($size > self::kB) {
				$size = round($size / self::kB, 1)." kB";
			}

			return $size;
		}

		/* List directory
		 */
		public function list($root, $url) {
			$this->path = explode("/", trim($url, "/"));

			$path = $root.$url;

			if (is_dir($path) == false) {
				return false;
			} else if (($dp = opendir($path)) === false) {
				return false;
			}

			$items = array();
			while (($item = readdir($dp)) != false) {
				if (substr($item, 0, 1) == ".") {
					continue;
				}
				array_push($items, $item);
			}
			sort($items);

			closedir($dp);

			$this->view->open_tag("directory", array("url" => $url));

			$this->show_path($url);
			$this->show_structure($root);

			if ($url != "") {
				array_unshift($items, "..");
			}

			$this->view->open_tag("list");
			foreach ($items as $item) {
				$type = is_dir($path."/".$item) ? "directory" : "file";

				ob_start();
				$stat = @stat($path."/".$item);
				ob_end_clean();

				$info = pathinfo($item);
				$item = array(
					"name" => $item,
					"link" => rawurlencode($item),
					"ext"  => $info["extension"] ?? "");

				if ($info != false) {
					$item["modified"] = date_string("j F Y, H:i:s", $stat["mtime"]);
					$item["size"] = $this->nice_size($stat["size"]);
				}
				$this->view->record($item, $type);
			}
			$this->view->close_tag();

			$this->view->close_tag();

			return true;
		}
	}
?>
