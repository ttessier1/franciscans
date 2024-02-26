<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/**
 * Plugin Name: Admin Tools for WordPress
 * Description: System wide plugin for enhanced protection
 * Version: 1.6.4
 * Author: Akeeba Ltd
 * Author URI: https://www.akeeba.com
 * License: GPLv3
 */

defined('WPINC') or die;
defined('ADMINTOOLSINC') or define('ADMINTOOLSINC', true);

if (!defined('ADMINTOOLSWP_PATH'))
{
	$plugin_dir  = get_option('admintoolswp_plugin_dir', 'admintoolswp');
	$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_dir;

	define('ADMINTOOLSWP_PATH', $plugin_path);

	unset($plugin_dir);
	unset($plugin_path);
}

// Let's double check that the file is at the same version of the component
define('ADMINTOOLSWP_MUPLUGIN_VERSION', '1.6.4');

// Skip version check if we're in Alpha or local dev
if (stripos(ADMINTOOLSWP_MUPLUGIN_VERSION, 'rev') === false)
{
	if (!defined('ADMINTOOLSWP_VERSION'))
	{
		// No version file? Be safe and stop here
		if (!file_exists(ADMINTOOLSWP_PATH . '/version.php'))
		{
			return;
		}

		require_once ADMINTOOLSWP_PATH . '/version.php';
	}

	// This should never happen, but let's be safe than sorry
	if (!defined('ADMINTOOLSWP_VERSION'))
	{
		return;
	}

	if (!version_compare(ADMINTOOLSWP_MUPLUGIN_VERSION, ADMINTOOLSWP_VERSION, 'eq'))
	{
		// Two different versions? Abort!
		return;
	}
}

// Check that we have the correct PHP version
$minPHP = defined('ADMINTOOLSWP_MIN_PHP') ? ADMINTOOLSWP_MIN_PHP : '7.4.0';

if (version_compare(PHP_VERSION, $minPHP, 'lt'))
{
	return;
}

unset($minPHP);

// Do not crash the site if anything is missing
if (file_exists(ADMINTOOLSWP_PATH . '/helpers/runplugins.php'))
{
	require ADMINTOOLSWP_PATH . '/helpers/runplugins.php';
}
