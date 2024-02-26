<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

$minPHP = defined('ADMINTOOLSWP_MIN_PHP') ? ADMINTOOLSWP_MIN_PHP : '7.4.0';

if (version_compare(PHP_VERSION, $minPHP, 'lt'))
{
	return;
}

// Why, oh why, are you people using eAccelerator? Seriously, what's wrong with you, people?!
if (function_exists('eaccelerator_info'))
{
	$isBrokenCachingEnabled = true;

	if (function_exists('ini_get') && !ini_get('eaccelerator.enable'))
	{
		$isBrokenCachingEnabled = false;
	}

	if ($isBrokenCachingEnabled)
	{
		return;
	}
}

// Include and initialise Admin Tools System Plugin autoloader
if (!defined('ATSYSTEM_AUTOLOADER'))
{
	@include_once __DIR__ . '/autoloader.php';
}

if (!defined('ATSYSTEM_AUTOLOADER') || !class_exists('AdmintoolsAutoloaderPlugin'))
{
	return;
}

AdmintoolsAutoloaderPlugin::init();

// fnmatch() doesn't exist in non-POSIX systems :(
if (!function_exists('fnmatch'))
{
	function fnmatch($pattern, $string)
	{
		return @preg_match(
			'/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
				array('*' => '.*', '?' => '.?')) . '$/i', $string
		);
	}
}

// Import main plugin file
if (!class_exists('AtsystemAdmintoolsMain', true))
{
	return;
}
