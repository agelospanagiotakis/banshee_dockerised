<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	// class captcha {
	// 	const FONT = "../extra/captcha_font.ttf";

	// 	private $code = null;
	// 	private $image = null;

	// 	/* Constructor
	// 	 *
	// 	 * INPUT:  [int image width[, int image height[, int number of characters]]]
	// 	 * OUTPUT: -
	// 	 * ERROR:  -
	// 	 */
	// 	public function __construct($width = 150, $height = 40, $characters = 8) {
	// 		$this->code = "";
	// 		$possible = "23456789bcdfghjkmnpqrstvwxyz";
	// 		$pos_max = strlen($possible) - 1;
	// 		for ($i = 0; $i < $characters; $i++) {
	// 			$this->code .= $possible[mt_rand(0, $pos_max)];
	// 		}

	// 		$font_size = $height * 0.75;
	// 		if (($image = imagecreate($width, $height)) == false) {
	// 			return;
	// 		}

	// 		$background_color = imagecolorallocate($image, 255, 255, 255);
	// 		$this->random_color($red, $green, $blue);
	// 		$dot_color = imagecolorallocate($image, $red, $green, $blue);
	// 		$this->random_color($red, $green, $blue);
	// 		$line_color = imagecolorallocate($image, $red, $green, $blue);
	// 		$this->random_color($red, $green, $blue);
	// 		$text_color = imagecolorallocate($image, $red, $green, $blue);

	// 		for ($i = 0; $i < ($width * $height) / 150; $i++) {
	// 			imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $line_color);
	// 		}

	// 		for ($i = 0; $i < ($width * $height) / 3; $i++) {
	// 			imagefilledellipse($image, mt_rand(0, $width), mt_rand(0, $height), 1, 1, $dot_color);
	// 		}

	// 		if (($textbox = imagettfbbox($font_size, 0, self::FONT, $this->code)) == false) {
	// 			return;
	// 		}

	// 		$x = ($width - $textbox[4]) / 2;
	// 		$y = ($height - $textbox[5]) / 2;

	// 		if (imagettftext($image, $font_size, 0, $x + 1, $y + 1, $background_color, self::FONT, $this->code) == false) {
	// 			return;
	// 		}
	// 		if (imagettftext($image, $font_size, 0, $x, $y, $text_color, self::FONT, $this->code) == false) {
	// 			return;
	// 		}

	// 		for ($i = 0; $i < 5; $i++) {
	// 			imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $dot_color);
	// 		}

	// 		$_SESSION["captcha_code"] = $this->code;
	// 		$this->image = $image;
	// 	}

	// 	/* Destructor
	// 	 *
	// 	 * INPUT:  -
	// 	 * OUTPUT: -
	// 	 * ERROR:  -
	// 	 */
	// 	public function __destruct() {
	// 		if ($this->image != false) {
	// 			imagedestroy($this->image);
	// 		}
	// 	}

	// 	/* Magic method get
	// 	 *
	// 	 * INPUT:  string key
	// 	 * OUTPUT: mixed value
	// 	 * ERROR:  null
	// 	 */
	// 	public function __get($key) {
	// 		switch ($key) {
	// 			case "created": return $this->image !== null;
	// 			case "code": return $this->code;
	// 		}

	// 		return null;
	// 	}

	// 	/* Generate random RGB color
	// 	 *
	// 	 * INPUT:  &int red, &int green, &int blue
	// 	 * OUTPUT: -
	// 	 * ERROR:  -
	// 	 */
	// 	private function random_color(&$red, &$green, &$blue) {
	// 		$max = array(255, 128, 40);
	// 		$result = array();

	// 		for ($i = 0; $i < 3; $i++) {
	// 			$skip = mt_rand(0, 2 - $i);
	// 			while ($skip-- > 0) {
	// 				array_push($max, array_shift($max));
	// 			}
	// 			$result[$i] = mt_rand(0, array_shift($max));
	// 		}

	// 		list($red, $green, $blue) = $result;
	// 	}

	// 	/* Validate captcha code
	// 	 *
	// 	 * INPUT:  string captcha code
	// 	 * OUTPUT: boolean captcha code valid
	// 	 * ERROR:  -
	// 	 */
	// 	public static function valid_code($code) {
	// 		if (isset($_SESSION["captcha_code"]) == false) {
	// 			return false;
	// 		}

	// 		return $_SESSION["captcha_code"] === $code;
	// 	}

	// 	/* Send captcha image to client
	// 	 *
	// 	 * INPUT:  -
	// 	 * OUTPUT: true
	// 	 * ERROR:  false
	// 	 */
	// 	public function to_output($view = null) {
	// 		if ($this->image === null) {
	// 			return false;
	// 		}

	// 		if ($view != null) {
	// 			$view->disable();
	// 		}

	// 		header("Content-Type: image/png");
	// 		header("Cache-Control: private, max-age=0, no-cache");
	// 		header("Pragma: no-cache");

	// 		return imagepng($this->image);
	// 	}
	// }
?>
