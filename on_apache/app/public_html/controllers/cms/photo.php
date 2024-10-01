<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_photo_controller extends Banshee\controller {
		private $modes = array("Normal", "Top / Left", "Center", "Bottom / Right");

		private function show_overview() {
			if (($albums = $this->model->get_albums()) === false) {
				return false;
			}
			if (($photos = $this->model->get_photos($_SESSION["photo_album"])) === false) {
				return false;
			}

			$this->view->add_javascript("webui/jquery-ui.js");
			$this->view->add_javascript("cms/photo.js");

			$this->view->open_tag("overview");

			$this->view->open_tag("albums", array("current" => $_SESSION["photo_album"]));
			foreach ($albums as $album) {
				$label = $album["name"].", ".date("d M Y", $album["timestamp"]);
				$this->view->add_tag("album", $label, array("id" => $album["id"]));
			}
			$this->view->close_tag();

			$this->view->open_tag("photos");
			foreach ($photos as $photo) {
				$photo["overview"] = show_boolean($photo["overview"]);
				$this->view->record($photo, "photo");
			}
			$this->view->close_tag();

			$this->view->open_tag("modes");
			foreach ($this->modes as $mode) {
				$this->view->add_tag("mode", $mode);
			}
			$this->view->close_tag();

			$this->view->close_tag();
		}

		private function show_edit_form($photo) {
			$this->view->open_tag("edit");
			$photo["overview"] = show_boolean($photo["overview"]);
			$this->view->record($photo, "photo");

			$this->view->open_tag("modes");
			foreach ($this->modes as $mode) {
				$this->view->add_tag("mode", $mode);
			}
			$this->view->close_tag();

			$this->view->close_tag();
		}

		public function execute() {
			if ($this->page->ajax_request) {
				/* Set photo order
				 */
				if ($_SERVER["REQUEST_METHOD"] == "POST") {
					$result = $this->model->position_photo($_POST["photo_id"], $_POST["position"]);
					$this->view->add_tag("result", $result ? "ok" : "fail");
				}

				return;
			}

			$this->page_size = $this->settings->admin_page_size;

			/* Work-around for the most fucking annoying crap browser in the world: IE
			 */
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				foreach ($_FILES as $i => $file) {
					if ($file["type"] == "image/pjpeg") {
						$files[$i]["type"] = "image/jpeg";
					}
				}

				if ((isset($_POST["title"]) == false) && isset($_POST["photo_album_id"])) {
					if (($count = $this->model->count_photos_in_album($_POST["photo_album_id"])) !== false) {
						$_POST["title"] = "Photo ".($count + 1);
					}
				}
			}

			if (isset($_SESSION["photo_album"]) == false) {
				if (($albums = $this->model->get_albums()) != false) {
					$last_date = 0;
					foreach ($albums as $album) {
						if ($album["timestamp"] > $last_date) {
							$_SESSION["photo_album"] = (int)$album["id"];
							$last_date = (int)$album["timestamp"];
						}
					}
				}
			}

			if (($album_count = $this->model->count_albums()) === false) {
				$this->view->add_tag("result", "Error counting albums");
				return;
			} else if ($album_count == 0) {
				$this->view->add_tag("result", "No albums available. Add some first.", array("url" => "cms/photo/album/new"));
				return;
			}

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "album") {
					/* Select album
					 */
					if ($this->model->valid_album_id($_POST["album"])) {
						$_SESSION["photo_album"] = (int)$_POST["album"];
					} else {
						$this->view->add_system_warning("Invalid album id");
					}
					$this->show_overview();
				} else if ($_POST["submit_button"] == "Upload photos") {
					/* Upload photos
					 */
					if ($this->model->upload_okay($_FILES["photos"]) == false) {
						$this->show_overview();
					} else if ($this->model->create_photos($_FILES["photos"], $_POST) == false) {
						$this->view->add_system_warning("Error uploading photo.");
						$this->show_overview();
					} else {
						$this->show_overview();
					}
				} else if ($_POST["submit_button"] == "Update photo") {
					/* Update photo
					 */
					if ($this->model->edit_okay($_POST) == false) {
						$this->show_edit_form($_POST);
					} else if ($this->model->update_photo($_POST) == false) {
						$this->show_edit_form($_POST);
					} else {
						$this->show_overview();
					}
				} else if ($_POST["submit_button"] == "Delete photo") {
					/* Delete photo
					 */
					if ($this->model->delete_photo($_POST["id"]) == false) {
						$this->view->add_message("Error while deleting photo.");
						$this->show_edit_form($_POST);
					} else {
						$this->show_overview();
					}
				} else {
					$this->show_overview();
				}
			} else if ($this->page->parameter_numeric(0)) {
				if (($photo = $this->model->get_photo($this->page->parameters[0])) != false) {
					$this->show_edit_form($photo);
				} else {
					$this->view->add_tag("result", "Photo not found.");
				}
			} else {
				$this->show_overview();
			}
		}
	}
?>
