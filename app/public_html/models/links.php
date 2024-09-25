<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class links_model extends Banshee\model {
		public function get_links() {
			$query = "select * from link_categories order by category";
			if (($categories = $this->db->execute($query)) === false) {
				return false;
			}

			$query = "select * from links where category_id=%d order by text";
			foreach ($categories as $c => $category) {
				if (($links = $this->db->execute($query, $category["id"])) === false) {
					return false;
				}
				$categories[$c]["links"] = $links;
			}

			return $categories;
		}
	}
?>
