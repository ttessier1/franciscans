<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Library\Encrypt;

defined('ADMINTOOLSINC') or die();

/**
 * Generates cryptographically-secure random values.
 */
class Randval implements RandvalInterface
{
	/**
	 *
	 * Returns a cryptographically secure random value.
	 *
	 * random_bytes() is PHP 7 or later but WordPress already includes a polyfill for PHP 5.
	 *
	 * @param   integer  $bytes  How many bytes to return
	 *
	 * @return  string
	 */
	public function generate($bytes = 32)
	{
		return random_bytes($bytes);
	}

	/**
	 * Generates a random string with the specified length. WARNING: You get to specify the number of
	 * random characters in the string, not the number of random bytes. The character pool is 64 characters
	 * (6 bits) long. The entropy of your string is 6 * $characters bits. This means that a random string
	 * of 32 characters has an entropy of 192 bits whereas a random sequence of 32 bytes returned by generate()
	 * has an entropy of 8 * 32 = 256 bits.
	 *
	 * @param int $characters
	 *
	 * @return string
	 */
	public function generateString($characters = 32)
	{
		$sourceString = str_split('abcdefghijklmnopqrstuvwxyz-ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789', 1);
		$ret = '';

		$bytes = ceil($characters / 4) * 3;
		$randBytes = $this->generate($bytes);

		for ($i = 0; $i < $bytes; $i += 3)
		{
			$subBytes = substr($randBytes, $i, 3);
			$subBytes = str_split($subBytes, 1);
			$subBytes = ord($subBytes[0]) * 65536 + ord($subBytes[1]) * 256 + ord($subBytes[2]);
			$subBytes = $subBytes & bindec('00000000111111111111111111111111');

			$b = array();
			$b[0] = $subBytes >> 18;
			$b[1] = ($subBytes >> 12) & bindec('111111');
			$b[2] = ($subBytes >> 6) & bindec('111111');
			$b[3] = $subBytes & bindec('111111');

			$ret .= $sourceString[$b[0]] . $sourceString[$b[1]] . $sourceString[$b[2]] . $sourceString[$b[3]];
		}

		return substr($ret, 0, $characters);
	}
}
