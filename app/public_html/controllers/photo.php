<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class photo_controller extends Banshee\controller {
		private $extensions = array(
			"gif" => "image/gif",
			"jpg" => "image/jpeg",
			"png" => "image/png");

		private function show_albums() {
			if (($count = $this->model->count_albums()) === false) {
				$this->view->add_tag("result", "Database error counting albums");
				return;
			}

			$pagination = new \Banshee\pagination($this->view, "photo_albums", $this->settings->photo_page_size, $count);

			if (($albums = $this->model->get_albums($pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Database error retrieving albums");
				return;
			} else if (count($albums) == 0) {
				$this->view->add_tag("result", "No photo albums have been created yet.", array("seconds" => -1));
				return;
			}

			$this->view->open_tag("overview");

			$this->view->open_tag("albums");
			foreach ($albums as $album) {
				$album["timestamp"] = date_string("j F Y", strtotime($album["timestamp"]));
				$this->view->record($album, "album");
			}
			$this->view->close_tag();

			$pagination->show_browse_links();

			$this->view->close_tag();
		}

		private function show_album($album_id) {
			if (($album = $this->model->get_album_info($album_id)) === false) {
				$this->view->add_tag("result", "Database error retrieving album information.");
				return;
			} else if ($album === null) {
				$this->view->add_tag("result", "Photo album not found.");
				return;
			}

			if (($count = $this->model->count_photos_in_album($album_id)) === false) {
				$this->view->add_tag("result", "database error counting albums");
				return;
			}

			$pagination = new \Banshee\pagination($this->view, "photo_album_".$album_id, $this->settings->photo_album_size, $count);

			if (($photos = $this->model->get_photo_info($album_id, $pagination->offset, $pagination->size)) === false) {
				$this->view->add_tag("result", "Database error retrieving photos.");
				return;
			} else if (count($photos) == 0) {
				$this->view->add_tag("result", "Photo album is empty.");
				return;
			}

			$this->view->title = sprintf("%s - %s", $album["name"], $this->view->title);

			$this->view->open_tag("photos", array(
				"timestamp" => date_string("j F Y", strtotime($album["timestamp"])),
				"info"      => $album["description"],
				"listed"    => show_boolean($album["listed"])));
			foreach ($photos as $photo) {
				$this->view->record($photo, "photo");
			}
			$pagination->show_browse_links();
			$this->view->close_tag();

			$this->view->add_javascript("banshee/jquery.magnific-popup.js");
			$this->view->add_javascript("photo.js");

			$this->view->add_css("banshee/magnific-popup.css");
		}

		private function show_photo($photo) {
			list($name, $extension) = explode(".", $photo, 2);

			if ($this->user->logged_in == false) {
				list(, $photo_id) = explode("_", $name, 2);
				if ($this->model->private_photo($photo_id)) {
					return false;
				}
			}

			if (isset($this->extensions[$extension]) == false) {
				return false;
			} else if (file_exists(PHOTO_PATH."/".$photo) == false) {
				return false;
			}

			$this->view->disable();

			header("Content-Type: ".$this->extensions[$extension]);
			readfile(PHOTO_PATH."/".$photo);

			return true;
		}

		public function execute() {
			$this->view->title = "Photos";

			if ($this->page->parameter_numeric(0)) {
				$this->show_album($this->page->parameters[0]);
			} else if (valid_input($this->page->parameters[0] ?? null, VALIDATE_NONCAPITALS.VALIDATE_NUMBERS."_.", VALIDATE_NONEMPTY)) {
				if ($this->show_photo($this->page->parameters[0]) == false) {
					header("Result: 404");
					$this->view->add_tag("result", "This image could not be found.");
				}
			} else {
				$this->show_albums();
			}
		}
	}
?>
