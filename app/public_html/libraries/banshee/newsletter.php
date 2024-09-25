<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	class newsletter extends Protocol\email {
		/* Constructor
		 *
		 * INPUT:  string subject[, string e-mail][, string name]
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($subject, $from_address = null, $from_name = null) {
			$subject = utf8_decode($subject);

			parent::__construct($subject, $from_address, $from_name);
		}

		/* Set newsletter content
		 *
		 * INPUT:  string content
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function message($content) {
			$content = utf8_decode($content);

			$content = str_replace("\n\n", "</p>\n<p>", $content);
			$content = str_replace("\n", "<br>\n", $content);

			$message = file_get_contents("../extra/newsletter.txt");
			$this->set_message_fields(array(
				"TITLE"       => $this->subject,
				"CONTENT"     => $content,
				"SERVER_NAME" => $_SERVER["SERVER_NAME"]));

			parent::message($message);
		}
	}
?>
