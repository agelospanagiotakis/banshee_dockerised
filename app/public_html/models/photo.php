<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class photo_model extends Banshee\model {
		public function count_albums() {
			$query = "select count(*) as count from photo_albums where listed=%d";
			$args = array(YES);
			if ($this->user->logged_in == false) {
				$query .= " and private=%d";
				array_push($args, NO);
			}

			if (($result = $this->db->execute($query, $args)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_albums($offset, $limit) {
			$query = "select * from photo_albums where listed=%d";
			$args = array(YES);
			if ($this->user->logged_in == false) {
				$query .= " and private=%d";
				array_push($args, NO);
			}
			$query .= " order by timestamp desc limit %d,%d";
			array_push($args, $offset, $limit);

			if (($albums = $this->db->execute($query, $args)) === false) {
				return false;
			}

			$query = "select * from photos where photo_album_id=%d and overview=%d";
			foreach ($albums as &$album) {
				if (($thumbnails = $this->db->execute($query, $album["id"], YES)) != false) {
					$photo = rand(0, count($thumbnails) - 1);
					$album["extension"] = $thumbnails[$photo]["extension"];
					$album["thumbnail"] = $thumbnails[$photo]["id"];
				}
				unset($album);
			}

			return $albums;
		}

		public function count_photos_in_album($album_id) {
			$query = "select count(*) as count from photos where photo_album_id=%d";

			if (($result = $this->db->execute($query, $album_id)) == false) {
				return false;
			}

			return $result[0]["count"];
		}

		public function get_album_info($album_id) {
			$query = "select * from photo_albums where id=%d";
			if ($this->user->logged_in == false) {
				$query .= " and private=0";
			}

			if (($result = $this->db->execute($query, $album_id)) === false) {
				return false;
			} else if (count($result) == 0) {
				return null;
			}

			return $result[0];
		}

		public function private_photo($photo_id) {
			$query = "select private from photos p, photo_albums a where p.photo_album_id=a.id and p.id=%d";

			if (($result = $this->db->execute($query, $photo_id)) == false) {
				return true;
			}

			return $result[0]["private"] == YES;
		}

		public function get_photo_info($album_id, $offset, $limit) {
			$query = "select * from photos where photo_album_id=%d ".
			         "order by %S limit %d,%d";

			return $this->db->execute($query, $album_id, "order", $offset, $limit);
		}
	}
?>
