<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_news_model extends Banshee\tablemanager_model {
		protected $table = "news";
		protected $order = "timestamp";
		protected $desc_order = true;
		protected $elements = array(
			"title" => array(
				"label"    => "Title",
				"type"     => "varchar",
				"overview" => true,
				"required" => true),
			"timestamp" => array(
				"label"    => "Published at",
				"type"     => "timestamp",
				"overview" => true),
			"content" => array(
				"label"    => "Content",
				"type"     => "ckeditor",
				"required" => true));
	}
?>
