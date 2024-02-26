<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Scanner\Logger;

defined('ADMINTOOLSINC') or die;

/**
 * Log levels
 */
abstract class LogLevel
{
	const ERROR = 1;
	const WARNING = 2;
	const INFO = 3;
	const DEBUG = 4;
}