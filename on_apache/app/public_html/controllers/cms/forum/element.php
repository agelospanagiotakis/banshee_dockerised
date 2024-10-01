<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_forum_element_controller extends Banshee\tablemanager_controller {
		protected $name = "Forum element";
		protected $back = "cms/forum";
		protected $icon = "forum.png";
		protected $page_size = 25;
		protected $foreign_null = "---";
		protected $browsing = null;
		protected $sortable = true;
	}
?>
