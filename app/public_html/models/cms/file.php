<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_file_model extends Banshee\model {
		private function filename_okay($file) {
			if (trim($file) == "") {
				return false;
			}

			if (substr($file, 0, 1) == ".") {
				return false;
			}

			return valid_input($file, VALIDATE_NUMBERS.VALIDATE_LETTERS."-_. ");
		}

		private function dirname_okay($file) {
			return valid_input($file, VALIDATE_NUMBERS.VALIDATE_LETTERS."-_", VALIDATE_NONEMPTY);
		}

		public function valid_path($path) {
			if (strpos($path, "//") !== false) {
				return false;
			}

			return valid_input($path, VALIDATE_NUMBERS.VALIDATE_LETTERS."-_/", VALIDATE_NONEMPTY);
		}

		public function directory_listing($directory) {
			if (($dp = opendir($directory)) == false) {
				return false;
			}

			$files = $dirs = array();
			while (($file = readdir($dp)) !== false) {
				if ($file[0] == ".") {
					continue;
				}
				if (is_dir($directory."/".$file)) {
					array_push($dirs, $file);
				} else {
					array_push($files, $file);
				}
			}

			closedir($dp);

			sort($files);
			sort($dirs);

			return array(
				"dirs"  => $dirs,
				"files" => $files);
		}

		public function get_file_size($file) {
			if (($size = filesize($file)) === false) {
				return false;
			}

			if ($size > MEGABYTE) {
				return sprintf("%.2f MB", $size / MEGABYTE);
			} else if ($size > KILOBYTE) {
				return sprintf("%.2f kB", $size / KILOBYTE);
			}

			return $size." byte";
		}

		public function upload_okay($file, $directory) {
			if ($file["error"] !== 0) {
				$this->view->add_system_warning("Error while uploading file.");
				return false;
			}

			if ($this->filename_okay($file["name"]) == false) {
				$this->view->add_message("Invalid filename.");
				return false;
			}
			if (($ext = strrchr($file["name"], ".")) === false) {
				$this->view->add_message("File has no extension.");
				return false;
			}
			if (in_array(substr(strtolower($ext), 1), config_array(ALLOWED_UPLOADS)) == false) {
				$this->view->add_message("Invalid file extension.");
				return false;
			}
			if (file_exists($directory."/".$file["name"])) {
				$this->view->add_message("File already exists.");
				return false;
			}

			return true;
		}

		public function import_uploaded_file($file, $directory) {
			return move_uploaded_file($file["tmp_name"], $directory."/".$file["name"]);
		}

		public function rename_file($current, $new, $directory) {
			if ($this->filename_okay($current) == false) {
				return false;
			}

			if ($this->filename_okay($new) == false) {
				return false;
			}

			$r_current = $directory."/".$current;
			$r_new = $directory."/".$new;

			if (is_dir($r_current)) {
				if ($this->dirname_okay($current) == false) {
					return false;
				}

				if ($this->dirname_okay($new) == false) {
					return false;
				}
			}

			return rename($r_current, $r_new);
		}

		public function delete_file($file, $directory) {
			if ($this->filename_okay($file) == false) {
				return false;
			}
			$file = $directory."/".$file;

			ob_start();
			$result = is_dir($file) ? rmdir($file) : unlink($file);
			ob_end_clean();

			return $result;
		}

		public function directory_empty($subdir, $directory) {
			if (($dp = opendir($directory."/".$subdir)) == false) {
				return false;
			}

			$result = true;
			$allowed = array(".", "..");
			while (($file = readdir($dp)) !== false) {
				if (in_array($file, $allowed) == false) {
					$result = false;
					break;
				}
			}
			closedir($dp);

			return $result;
		}

		public function directory_okay($subdir, $directory) {
			$result = true;

			if ($this->dirname_okay($subdir) == false) {
				$this->view->add_message("Invalid directory name.");
				$result = false;
			} else if (file_exists($directory."/".$subdir)) {
				$this->view->add_message("Directory already exists.");
				$result = false;
			}

			return $result;
		}

		public function create_directory($subdir, $directory) {
			return @mkdir($directory."/".$subdir);
		}
	}
?>
