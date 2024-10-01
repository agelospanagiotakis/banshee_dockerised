<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class cms_link_category_controller extends Banshee\tablemanager_controller {
		protected $name = "Category";
		protected $back = "cms/link";
		protected $icon = "links.png";
		protected $page_size = 25;
		protected $browsing = null;
	}
?>
