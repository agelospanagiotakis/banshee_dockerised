<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_photo_album_model extends Banshee\tablemanager_model {
		protected $table = "photo_albums";
		protected $elements = array(
			"name" => array(
				"label"    => "Name",
				"type"     => "varchar",
				"overview" => true,
				"required" => true),
			"description" => array(
				"label"    => "Description",
				"type"     => "text",
				"overview" => false,
				"required" => true),
			"timestamp" => array(
				"label"    => "Timestamp",
				"type"     => "date",
				"overview" => true,
				"readonly" => false),
			"listed" => array(
				"label"    => "Listed in index",
				"type"     => "boolean",
				"overview" => true,
				"default"  => true),
			"private" => array(
				"label"    => "Private",
				"type"     => "boolean",
				"overview" => true));

		public function create_item($item) {
			$item["timestamp"] = date("Y-m-d");

			parent::create_item($item);
		}

		public function delete_okay($item_id) {
			$query = "select count(*) as count from photos where photo_album_id=%d";

			if (($result = $this->db->execute($query, $item_id)) === false) {
				$this->view->add_system_warning("Error counting photos in album.");
				return false;
			} else if ($result[0]["count"] > 0) {
				$this->view->add_message("Photo album contains photos. Delete them first.");
				return false;
			}

			return true;
		}

		public function delete_item($item_id) {
			$query = "select * from photos where photo_album_id=%d";
			if (($photos = $this->db->execute($query, $item_id)) === false) {
				return false;
			}

			$queries = array(
				array("delete from photos where photo_album_id=%d", $item_id),
				array("delete from collection_album where album_id=%d", $item_id),
				array("delete from %S where id=%d", $this->table, $item_id));

			if ($this->db->transaction($queries) == false) {
				return false;
			}

			foreach ($photos as $photo) {
				unlink(PHOTO_PATH."/image_".$photo["id"].".".$photo["extension"]);
				unlink(PHOTO_PATH."/thumbnail_".$photo["id"].".".$photo["extension"]);
			}

			return true;
		}
	}
?>
