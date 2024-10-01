<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee;

	class dynamic_view extends Core\XML {
		private $view = null;

		public function __construct($database, $view) {
			$this->view = $view;

			parent::__construct($database);
		}

		public function add_css($css, $prepend = false) {
			$this->view->add_css($css, $prepend);
		}

		public function add_inline_css($css) {
			$this->view->add_inline_css($css);
		}

		public function add_javascript($script) {
			$this->view->add_javascript($script);
		}

		public function run_javascript($code) {
			$this->view->run_javascript($code);
		}

		public function add_help_button() {
			$this->view->add_help_button();
		}
	}

	abstract class dynamic_blocks extends model {
		const TAG_OPEN = "{[";
		const TAG_CLOSE = "]}";

		private $class = null;
		protected $xslt_path = "views/banshee";

		/* constructor
		 *
		 * input:  object database, object settings
		 * output: -
		 * error:  -
		 */
		public function __construct($database, $settings, $user, $page, $view, $language = null) {
			$xml = new dynamic_view($database, $view);

			$arguments = array($database, $settings, $user, $page, $xml, $language);
			call_user_func_array(array(parent::class, "__construct"), $arguments);

			$this->class = array_pop(explode("\\", static::class));
		}

		/* Available sections
		 *
		 * INPUT:  -
		 * OUTPUT: array available sections
		 * ERROR:  -
		 */
		static public function available_sections() {
			$methods = get_class_methods(static::class);
			$remove = array("__construct", "borrow", "available_sections",
			               "get_dynamic_content", "execute");
			$methods = array_diff($methods, $remove);
			sort($methods);

			return $methods;
		}

		/* Execute
		 *
		 * INPUT:  string dynamic format
		 * OUTPUT: string rendered content
		 * ERROR:  -
		 */
		private function get_dynamic_content($section, $parameters) {
			if (in_array($section, $this->available_sections()) == false) {
				return null;
			}

			$this->view->clear_buffer();

			$this->view->open_tag($section);
			if (($result = call_user_func_array(array($this, $section), $parameters)) !== null) {
				return htmlentities($result);
			}
			$this->view->close_tag();

			$html = $this->view->transform(__DIR__."/../../".$this->xslt_path."/".$this->class.".xslt");

			if (substr($html, 0, 9) == "<!DOCTYPE") {
				if (($pos = strpos($html, "<", 1)) !== false) {
					$html = substr($html, $pos);
				}
			}

			return $html;
		}

		/* Execute
		 *
		 * INPUT:  string dynamic format
		 * OUTPUT: string rendered content
		 * ERROR:  -
		 */
		public function execute($content) {
			$tags_replaced = 0;
			$tag_open_length = strlen(self::TAG_OPEN);
			$tag_close_length = strlen(self::TAG_CLOSE);

			$end = 0;
			while (($begin = strpos($content, self::TAG_OPEN, $end)) !== false) {
				if (($end = strpos($content, self::TAG_CLOSE, $begin)) === false) {
					break;
				}

				$begin_c = $begin + $tag_open_length;
				$block = substr($content, $begin_c, $end - $begin_c);
				$end += $tag_close_length;
				$block_len = $end - $begin;

				$parameters = explode(" ", trim($block));
				$section = array_shift($parameters);

				if (($block = $this->get_dynamic_content($section, $parameters)) !== null) {
					$block = rtrim($block);
					$tags_replaced++;
				} else {
					$block = "";
				}

				$content = substr($content, 0, $begin) . $block . substr($content, $end);

				$end += strlen($block) - $block_len;
			}

			if ($tags_replaced > 0) {
				$this->view->add_css($this->xslt_path."/".$this->class.".css");
			}

			return $content;
		}
	}
?>
