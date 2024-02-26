<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Library\Database;

defined('ADMINTOOLSINC') or die;


/**
 * Database Interface
 *
 * @codeCoverageIgnore
 */
interface DatabaseInterface
{
	/**
	* Test to see if the connector is available.
	*
	* @return  boolean  True on success, false otherwise.
	*/
	public static function isSupported();
}
