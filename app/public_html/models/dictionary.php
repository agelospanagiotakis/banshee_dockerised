<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	class dictionary_model extends Banshee\model {
		public function get_first_letters() {
			$query = "select distinct lower(substring(word, 1, 1)) as %S from dictionary order by %S";

			return $this->db->execute($query, "char", "char");
		}

		public function get_words($first_letter) {
			$query = "select * from dictionary where word like %s order by word";

			return $this->db->execute($query, $first_letter."%");
		}

		public function get_word($word_id) {
			return $this->db->entry("dictionary", $word_id);
		}
	}
?>
