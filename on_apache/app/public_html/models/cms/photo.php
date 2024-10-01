<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_photo_model extends Banshee\model {
		const THUMBNAIL_MODE_NORMAL = 0;
		const THUMBNAIL_MODE_TOP_LEFT = 1;
		const THUMBNAIL_MODE_CENTER = 2;
		const THUMBNAIL_MODE_BOTTOM_RIGHT = 3;

		protected $table = "photos";
		protected $order = "title";
		protected $desc_order = true;
		protected $extensions = array(
			"image/gif"  => "gif",
			"image/jpeg" => "jpg",
			"image/png"  => "png");

		public function count_albums() {
			$query = "select count(*) as count from photo_albums";

			if (($result = $this->db->execute($query)) == false) {
				return false;
			}

			return (int)$result[0]["count"];
		}

		public function count_photos_in_album($album_id) {
			$query = "select count(*) as count from photos where photo_album_id=%d";
			if (($result = $this->db->execute($query, $album_id)) == false) {
				return false;
			}

			return (int)$result[0]["count"];
		}

		public function get_albums() {
			$query = "select id,name,UNIX_TIMESTAMP(timestamp) as timestamp from photo_albums order by name";

			return $this->db->execute($query);
		}

		public function valid_album_id($album_id) {
			$query = "select count(*) as count from photo_albums where id=%d";
			if (($result = $this->db->execute($query, $album_id)) === false) {
				return false;
			}

			return $result[0]["count"] > 0;
		}

		public function get_photo($photo_id) {
			return $this->db->entry("photos", $photo_id);
		}

		public function get_photos($album_id) {
			$query = "select * from photos where photo_album_id=%d order by %S";

			return $this->db->execute($query, $album_id, "order");
		}

		public function upload_okay($photos) {
			if ($photos["error"][0] == 4) {
				$this->view->add_message("No photos were uploaded.");
				return false;
			}

			$result = true;

			$allowed_types = array_keys($this->extensions);
			$count = count($photos["name"]);
			for ($i = 0; $i < $count; $i++) {
				if ($photos["error"][$i] != 0) {
					$this->view->add_system_warning("Error while uploading %s.", $photos["name"][$i]);
					$result = false;
				} else if (in_array($photos["type"][$i], $allowed_types) == false) {
					$this->view->add_message("Incorrect file type for %s.", $photos["name"][$i]);
					$result = false;
				}
			}

			return $result;
		}

		public function edit_okay($photo) {
			$result = true;

			if (trim($photo["title"]) == "") {
				$this->view->add_message("Enter a title.");
				$result = false;
			}

			return $result;
		}

		private function save_image($photo) {
			$image = new \Banshee\image($photo["file"]);

			if (($image->width > $this->settings->photo_image_height) || ($image->height > $this->settings->photo_image_width)) {
				$image->resize($this->settings->photo_image_height, $this->settings->photo_image_width);
			}

			if ($image->save(PHOTO_PATH."/image_".$photo["id"].".".$photo["extension"]) == false) {
				return false;
			}
			unset($image);

			return true;
		}

		private function save_thumbnail($photo) {
			$image = new \Banshee\image($photo["file"]);

			if ($photo["mode"] != self::THUMBNAIL_MODE_NORMAL) {
				if ($image->width > $image->height) {
					$long = $image->width;
					$short = $image->height;
				} else {
					$long = $image->height;
					$short = $image->width;
				}

				switch ($photo["mode"]) {
					case self::THUMBNAIL_MODE_TOP_LEFT:
						$x = $y = 0;
						break;
					case self::THUMBNAIL_MODE_CENTER:
						$x = ($long - $short) / 2;
						$y = 0;
						if ($image->height > $image->width) {
							list($x, $y) = array($y, $x);
						}
						break;
					case self::THUMBNAIL_MODE_BOTTOM_RIGHT:
						$x = $long - $short;
						$y = 0;
						if ($image->height > $image->width) {
							list($x, $y) = array($y, $x);
						}
						break;
				}

				$image->crop($x, $y, $short, $short);
			}

			$image->resize($this->settings->photo_thumbnail_height, $this->settings->photo_thumbnail_width);

			if ($image->save(PHOTO_PATH."/thumbnail_".$photo["id"].".".$photo["extension"]) == false) {
				return false;
			}
			unset($image);

			return true;
		}

		private function set_extension(&$item) {
			if (isset($_FILES["image"]) == false) {
				return true;
			} else if ($_FILES["image"]["error"] != 0) {
				return true;
			}

			$type = $_FILES["image"]["type"];
			if (isset($this->extensions[$type]) == false) {
				return false;
			}

			$item["extension"] = $this->extensions[$type];

			return true;
		}

		public function position_photo($photo_id, $position) {
			if (($photo = $this->get_photo($photo_id)) == false) {
				return false;
			}

			if ($photo["order"] > $position) {
				$first = $position;
				$last = $photo["order"];
				$mode = "+";
			} else if ($photo["order"] < $position) {
				$first = $photo["order"];
				$last = $position;
				$mode = "-";
			} else {
				return true;
			}

			$query = "update photos set %S=%S".$mode."1 where photo_album_id=%d and %S>=%d and %S<=%d";
			$this->db->query($query, "order", "order", $photo["photo_album_id"],
			                         "order", $first, "order", $last);

			$this->db->update("photos", $photo_id, array("order" => $position));

			return true;
		}

		public function create_photos($photos, $settings) {
			$count = count($photos["name"]);
			$photo_count = $this->count_photos_in_album($_SESSION["photo_album"]);

			for ($i = 0; $i < $count; $i++) {
				if ($photos["error"][$i] != 0) {
					continue;
				}

				$extension = $this->extensions[$photos["type"][$i]];

				$data = array(
					"id"             => null,
					"title"          => "Photo ".($photo_count + 1),
					"photo_album_id" => $_SESSION["photo_album"],
					"extension"      => $extension,
					"overview"       => is_true($settings["overview"] ?? false) ? YES : NO,
					"thumbnail_mode" => $settings["mode"],
					"order"          => $photo_count);

				$this->db->query("begin");

				if ($this->db->insert("photos", $data) == false) {
					$this->db->query("rollback");
					continue;
				}

				$photo = array(
					"id"        => $this->db->last_insert_id,
					"file"      => $photos["tmp_name"][$i],
					"extension" => $extension,
					"mode"      => $settings["mode"]);

				if ($this->save_image($photo) == false) {
					$this->db->query("rollback");
				} else if ($this->save_thumbnail($photo) == false) {
					$this->db->query("rollback");
					unlink(PHOTO_PATH."/image_".$photo["id"].".".$extension);
				} else {
					$this->db->query("commit");
				}

				$photo_count++;
			}

			return true;
		}

		public function update_photo($photo) {
			if (($current = $this->get_photo($photo["id"])) == false) {
				return false;
			}

			$data = array(
				"title"          => $photo["title"],
				"overview"       => is_true($photo["overview"] ?? false) ? YES : NO,
				"thumbnail_mode" => $photo["mode"]);

			if ($this->db->update("photos", $photo["id"], $data) === false) {
				return false;
			}

			$photo["extension"] = $current["extension"];
			$photo["file"] = PHOTO_PATH."/image_".$photo["id"].".".$photo["extension"];
			unlink(PHOTO_PATH."/thumbnail_".$photo["id"].".".$photo["extension"]);
			if ($this->save_thumbnail($photo) == false) {
				return false;
			}

			return true;
		}

		public function delete_photo($photo_id) {
			if (($photo = $this->get_photo($photo_id)) == false) {
				return false;
			}
			$extension = $photo["extension"];

			$this->db->query("begin");

			if ($this->db->delete("photos", $photo_id) === false) {
				$this->db->query("rollback");
				return false;
			}

			$query = "update photos set %S=%S-1 where photo_album_id=%d and %S>%d";
			if ($this->db->query($query, "order", "order", $photo["photo_album_id"], "order", $photo["order"]) === false) {
				$this->db->query("rollback");
				return false;
			}

			$this->db->query("commit");

			$files = array(
				PHOTO_PATH."/image_".$photo_id.".".$extension,
				PHOTO_PATH."/thumbnail_".$photo_id.".".$extension);
			foreach ($files as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}

			return true;
		}
	}
?>
