<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

// Uncomment the following line to disable the auto-prepend mode
// return;

// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.
if (file_exists(__DIR__.'/##RUNPLUGINS##'))
{
	defined('ADMINTOOLSINC') or define('ADMINTOOLSINC', true);

	define('ADMINTOOLSWP_AUTOPREPEND_VERSION', '##VERSION##');
	$version_file = __DIR__.'/##ADMINTOOLSPATH##/version.php';

	if (!defined('ADMINTOOLSWP_VERSION'))
	{
		// No version file? Be safe and stop here
		if (!file_exists($version_file))
		{
			return;
		}

		require_once $version_file;
	}

	// This should never happen, but let's be safe than sorry
	if (!defined('ADMINTOOLSWP_VERSION'))
	{
		return;
	}

	if (! version_compare(ADMINTOOLSWP_AUTOPREPEND_VERSION, ADMINTOOLSWP_VERSION, 'eq'))
	{
		// Two different versions? Abort! Abort!
		return;
	}

	// Check that we have the correct PHP version
	$minPHP = defined('ADMINTOOLSWP_MIN_PHP') ? ADMINTOOLSWP_MIN_PHP : '7.4.0';

	if (version_compare(PHP_VERSION, $minPHP, 'lt'))
	{
		return;
	}

	unset($minPHP);
	unset($version_file);

	define('ADMINTOOLSWP_AUTOPREPEND', true);

	require __DIR__.'/##RUNPLUGINS##';
}
