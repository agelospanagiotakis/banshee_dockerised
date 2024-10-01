<?php
	/* Copyright (c) by Hugo Leisink <hugo@leisink.net>
	 * This file is part of the Banshee PHP framework
	 * https://gitlab.com/hsleisink/banshee/
	 *
	 * Licensed under The MIT License
	 */

	namespace Banshee\Protocol;

	class RSA {
		private $private_key = null;
		private $public_key = null;
		private $bits = null;
		private $type = null;
		private $max_length = null;

		/* Constructor
		 *
		 * INPUT:  string RSA key PEM | integer key size
		 * OUTPUT: -
		 * ERROR:  -
		 */
		public function __construct($key) {
			if (is_integer($key)) {
				/* Generate keys
				 */
				$config = array(
					"private_key_bits" => $key,
					"private_key_type" => OPENSSL_KEYTYPE_RSA);
				$rsa_key = openssl_pkey_new($config);

				openssl_pkey_export($rsa_key, $this->private_key);

				$details = openssl_pkey_get_details($rsa_key);
				$this->public_key = $details["key"];
			} else if (substr($key, 5, 17) == "BEGIN PRIVATE KEY") {
				if (($rsa_key = openssl_pkey_get_private($key)) === false) {
					return;
				}
				$details = openssl_pkey_get_details($rsa_key);

				$this->private_key = $key;
				$this->public_key = $details["key"];
			} else if (substr($key, 5, 16) == "BEGIN PUBLIC KEY") {
				if (($rsa_key = openssl_pkey_get_public($key)) === false) {
					return;
				}
				$details = openssl_pkey_get_details($rsa_key);
				$this->public_key = $key;
			} else {
				return;
			}

			$this->bits = $details["bits"];
			$this->type = $details["type"];
			$this->max_length = $details["bits"] / 8;
		}

		/* Magic method get
		 *
		 * INPUT:  string key
		 * OUTPUT: mixed value
		 * ERROR:  null
		 */
		public function __get($key) {
			switch ($key) {
				case "private_key": return $this->private_key;
				case "public_key": return $this->public_key;
				case "bits": return $this->bits;
				case "type": return $this->type;
				case "max_length": return $this->max_length;
			}

			return null;
		}

		/* Encrypt message with private key
		 *
		 * INPUT:  string message
		 * OUTPUT: string encrypted message
		 * ERROR:  false
		 */
		public function encrypt_with_private_key($message) {
			if ($this->private_key === null) {
				return false;
			} else if (strlen($message) > $this->max_length) {
				return false;
			}

			if (openssl_private_encrypt($message, $result, $this->private_key, OPENSSL_PKCS1_PADDING) == false) {
				return false;
			}

			return base64_encode($result);
		}

		/* Encrypt message with public key
		 *
		 * INPUT:  string message
		 * OUTPUT: string encrypted message
		 * ERROR:  false
		 */
		public function encrypt_with_public_key($message) {
			if ($this->public_key === null) {
				return false;
			} else if (strlen($message) > $this->max_length) {
				return false;
			}

			if (openssl_public_encrypt($message, $result, $this->public_key, OPENSSL_PKCS1_OAEP_PADDING) == false) {
				return false;
			}

			return base64_encode($result);
		}

		/* Decrypt message with private key
		 *
		 * INPUT:  string message
		 * OUTPUT: string decrypted message
		 * ERROR:  false
		 */
		public function decrypt_with_private_key($message) {
			if ($this->private_key === null) {
				return false;
			}

			$message = base64_decode($message, true);
			if (openssl_private_decrypt($message, $result, $this->private_key, OPENSSL_PKCS1_OAEP_PADDING) == false) {
				return false;
			}

			return $result;
		}

		/* Decrypt message with public key
		 *
		 * INPUT:  string message
		 * OUTPUT: string decrypted message
		 * ERROR:  false
		 */
		public function decrypt_with_public_key($message) {
			if ($this->public_key === null) {
				return false;
			}

			$message = base64_decode($message, true);
			if (openssl_public_decrypt($message, $result, $this->public_key, OPENSSL_PKCS1_PADDING) == false) {
				return false;
			}

			return $result;
		}
	}
?>
