<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_photo_collection_controller extends Banshee\controller {
		private function show_collection_overview() {
			if (($collections = $this->model->get_collections()) === false) {
				$this->add_tag("result", "Database error.");
				return;
			}

			$this->view->open_tag("overview");

			$this->view->open_tag("collections");
			foreach ($collections as $collection) {
				$this->view->record($collection, "collection");
			}
			$this->view->close_tag();

			$this->view->close_tag();
		}

		private function show_collection_form($collection) {
			if (($albums = $this->model->get_albums()) === false) {
				$this->view->add_tag("result", "Database error.");
				return;
			}

			if (count($albums) == 0) {
				$this->view->add_tag("result", "No albums available. Add some first.", array("url" => "cms/photo/album/new"));
				return;
			}

			if (is_array($collection["albums"] ?? null) == false) {
				$collection["albums"] = array();
			}

			$this->view->open_tag("edit");

			$params = isset($collection["id"]) ? array("id" => $collection["id"]) : array();

			$this->view->open_tag("collection", $params);
			$this->view->record($collection);

			$this->view->open_tag("albums");
			foreach ($albums as $album) {
				$this->view->add_tag("album", $album["name"], array(
					"id"      => $album["id"],
					"checked" => show_boolean(in_array($album["id"], $collection["albums"]))));
			}
			$this->view->close_tag();
			$this->view->close_tag();

			$this->view->close_tag();
		}

		public function execute() {
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if ($_POST["submit_button"] == "Save collection") {
					/* Save collection
					 */
					if ($this->model->save_okay($_POST) == false) {
						$this->show_collection_form($_POST);
					} else if (isset($_POST["id"]) == false) {
						/* Create collection
					 	 */
						if ($this->model->create_collection($_POST) == false) {
							$this->show_collection_form($_POST);
						} else {
							$this->show_collection_overview();
						}
					} else {
						/* Update collection
					 	 */
						if ($this->model->update_collection($_POST) == false) {
							$this->show_collection_form($_POST);
						} else {
							$this->show_collection_overview();
						}
					}
				} else if ($_POST["submit_button"] == "Delete collection") {
					/* Delete collection
					 */
					if ($this->model->delete_collection($_POST["id"]) == false) {
						$this->view->add_message("Error deleting collection.");
						$this->show_collection_form($_POST);
					} else {
						$this->show_collection_overview();
					}
				} else {
					$this->show_collection_overview();
				}
			} else if ($this->page->parameter_value(0, "new")) {
				$collection = array();
				$this->show_collection_form($collection);
			} else if ($this->page->parameter_numeric(0)) {
				if (($collection = $this->model->get_collection($this->page->parameters[0])) == false) {
					$this->view->add_tag("result", "Collection not found.");
				} else {
					$this->show_collection_form($collection);
				}
			} else {
				$this->show_collection_overview();
			}
		}
	}
?>
