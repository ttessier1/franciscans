<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Scanner\Exception;

defined('ADMINTOOLSINC') or die;

/**
 * Indicates a fatal exception which prevents restarting the execution
 */
class ErrorException extends FileScannerException
{

}