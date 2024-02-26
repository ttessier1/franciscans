<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Session;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\Scanner\Complexify;
use Akeeba\AdminTools\Admin\Model\Scans;
use Akeeba\AdminTools\Library\Encrypt\Base32;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Uri\Uri;

function getCompressionType()
{
	if (function_exists('bzcompress') && function_exists('bzdecompress'))
	{
		return 'bzip';
	}

	if (function_exists('gzcompress') && function_exists('gzuncompress'))
	{
		return 'gzip';
	}

	return 'raw';
}

function encodeData($rawData)
{
	if (empty($rawData))
	{
		return '';
	}

	switch (getCompressionType())
	{
		case 'bzip':
			$rawData = bzcompress($rawData, 9);

			$rawData = is_numeric($rawData) ? '' : $rawData;
			break;

		case 'gzip':
			$rawData = gzcompress($rawData, 9);

			$rawData = ($rawData === false) ? '' : $rawData;
			break;
	}

	if (empty($rawData))
	{
		return '';
	}

	$base32 = new Base32();

	try
	{
		return $base32->encode($rawData);
	}
	catch (Exception $e)
	{
		return '';
	}
}

function decodeData($data)
{
	if (empty($data))
	{
		return '';
	}

	$base32 = new Base32();

	try
	{
		$data = $base32->decode($data);
	}
	catch (Exception $e)
	{
		$data = '';
	}

	if (empty($data))
	{
		return '';
	}

	switch (getCompressionType())
	{
		case 'bzip':
			$ret = bzdecompress($data, 1);

			return is_numeric($ret) ? '' : $ret;
			break;

		case 'gzip':
			$ret = gzuncompress($data);

			return ($ret === false) ? '' : $ret;
			break;

		default:
			return $data;
	}
}

define('WPINC', 1);
define('ADMINTOOLSINC', 1);

if (!defined('ADMINTOOLSWP_PATH'))
{
	define('ADMINTOOLSWP_PATH', __DIR__);
}

require_once ADMINTOOLSWP_PATH . '/helpers/bootstrap.php';

// Try our very best to detect WordPress root path.
// We can safely do that since WP will check if this constant is already defined
if (!defined('ABSPATH'))
{
	$root = Wordpress::getSiteRoot();
	define('ABSPATH', $root);
}

$input = new Input();

// Unset time limits
$safe_mode = true;

if (function_exists('ini_get'))
{
	$safe_mode = ini_get('safe_mode');
}

if (!$safe_mode && function_exists('set_time_limit'))
{
	@set_time_limit(0);
}

// Is frontend backup enabled and is the Secret Key strong enough??
$params     = Params::getInstance();
$febEnabled = $params->getValue('frontend_enable', 0) != 0;
$validKey   = $params->getValue('frontend_secret_word', '');

if (!Complexify::isStrongEnough($validKey, false))
{
	$febEnabled = false;
}

if (!$febEnabled)
{
	@ob_end_clean();
	echo '403 ' . Language::_('COM_ADMINTOOLS_ERROR_NOT_ENABLED');
	flush();
	exit();
}

// Is the key good?
$key          = $input->get('key', '', 'raw');
$validKeyTrim = trim($validKey);

if (($key != $validKey) || (empty($validKeyTrim)))
{
	@ob_end_clean();
	echo '403 ' . Language::_('COM_ADMINTOOLS_ERROR_INVALID_KEY');
	flush();
	exit();
}

// Get the Scans model and start or step through scanning
$model  = new Scans($input);

try
{
	$sessionJson = decodeData($input->getString('session_json', ''));
}
catch (Exception $e)
{
	$sessionJson = '{}';
}

Session::setData($sessionJson);

if ($input->get('task', '') != 'step')
{
	$model->removeIncompleteScans();

	Session::setData('{}');

	$array = $model->startScan('frontend');
}
else
{
	$array = $model->stepScan();
}

$sessionJson = encodeData(Session::dumpData());

if ($array['error'] != '')
{
	// An error occurred
	@ob_end_clean();
	header('HTTP/1.1 500 File scanning failed');
	header('Content-Type: text/plain');
	header('Connection: close');
	flush();
	die('500 ERROR -- ' . $array['error']);
}

if ($array['done'])
{
	// All done
	@ob_end_clean();
	header('Content-type: text/plain');
	header('Connection: close');
	flush();
	echo '200 OK';

	exit();
}

$uri = Uri::getInstance();
$uri->delVar('key');
$uri->setVar('task', 'step');
$uri->setVar('session_json', $sessionJson);

$redirectionUrl = $uri->toString() . '&key=' . urlencode($key);

@ob_end_clean();
header('HTTP/1.1 302 Found');
header('Location: ' . $redirectionUrl);
header('Content-Type: text/plain');
header('Connection: close');
exit();