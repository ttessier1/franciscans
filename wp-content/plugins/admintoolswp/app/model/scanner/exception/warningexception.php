<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Scanner\Exception;

defined('ADMINTOOLSINC') or die;

/**
 * Indicates a non-fatal exception which should be reported but does not prevent restarting the execution
 */
class WarningException extends FileScannerException
{

}