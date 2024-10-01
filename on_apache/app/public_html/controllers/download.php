<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class download_controller extends Banshee\controller {
		protected $prevent_repost = false;
		protected $download_use_sendfile = DOWNLOAD_USE_SENDFILE;

		public function execute() {
			$this->view->add_javascript("download.js");

			$root = __DIR__."/../".DOWNLOAD_PATH;
			list($url) = explode("?", $_SERVER["REQUEST_URI"], 2);
			$url = str_replace("/".$this->page->module, "", $url);
			$url = urldecode(rtrim($url, "/"));
			$target = $root.$url;

			if (is_file($target)) {
				$this->view->disable();

				if (is_true($this->download_use_sendfile)) {
					header("X-Sendfile: ".$target);
				} else {
					if (($mimetype = $this->model->get_mimetype($target)) != false) {
						header("Content-Type: ".$mimetype);
					}
					readfile($target);
				}
				exit;
			}

			list($uri) = explode("?", $_SERVER["REQUEST_URI"], 2);
			if (substr($uri, -1) != "/") {
				header("Status: 301");
				header("Location: ".$uri."/");
				exit;
			}

			$directory = new \Banshee\DirectoryIndex($this->view);
			$directory->list($root, $url);
		}
	}
?>
