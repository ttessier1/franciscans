<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/*
 * Since we could run in "auto-prepend" mode, we will have to run Admin Tools twice: once to block all incoming requests
 * and malicious users and another one to hook to WP events (user logging in etc etc). This is the main entry point for the
 * mu plugin and the auto-prepend script: since WP is doing include_once on mu files the second time PHP interpreter will
 * simply ignore the file because it was already included before.
 */

defined('ADMINTOOLSINC') or die;

if (!defined('ADMINTOOLSWP_PATH'))
{
	define('ADMINTOOLSWP_PATH', realpath(__DIR__.'/../'));
}

// If the main.php.bak file has been renamed the user is trying to rescue himself. Do not run in this case
if (!file_exists(ADMINTOOLSWP_PATH . '/app/plugins/waf/admintools/main.php'))
{
	return;
}

// Do not run the WAF plugins under WP-CLI command line
if (defined('WP_CLI') && WP_CLI)
{
	return;
}


// Check if we have HTTP* headers in the $_SERVER super variable. If not, it means that we were being included from some
// external script and we're in "CLI" even if it's not a real CLI
$found = false;

foreach($_SERVER as $key => $value)
{
	if (strpos($key, 'HTTP') === 0)
	{
		$found = true;
	}
}

if (!$found)
{
	return;
}

$admintools = ADMINTOOLSWP_PATH.'/app/plugins/waf/admintools.php';

if (!file_exists($admintools))
{
	return;
}

$autoprepend = defined('ADMINTOOLSWP_AUTOPREPEND') && ADMINTOOLSWP_AUTOPREPEND;
$first_run   = !defined('ADMINTOOLSWP_FIRST_RUN');

if (!defined('ADMINTOOLSWP_FIRST_RUN'))
{
	define('ADMINTOOLSWP_FIRST_RUN', true);
}

require_once ADMINTOOLSWP_PATH.'/helpers/bootstrap.php';

// Let's check if our plugin is really active or it was disabled
$active 	   = false;
$currentFolder = basename(realpath(__DIR__.'/../'));

foreach (array(
			 $currentFolder . '/admintoolswp.php',
			 $currentFolder . '/' . $currentFolder. '.php',
		 ) as $plugin)
{
	if (\Akeeba\AdminTools\Admin\Helper\Wordpress::isPluginActive($plugin))
	{
		$active = true;
	}
}

// We've been disabled, let's shutdown
if (!$active)
{
	return;
}

require_once $admintools;

$input    = new \Akeeba\AdminTools\Library\Input\Input();
$ATplugin = new plgSystemAdmintools($input);

// Run the "system" features just once
if ($first_run)
{
	$ATplugin->onSystem();
}

// If we're not in auto-prepend mode or it's the second call, hook to Wordpress features
if (!$autoprepend || !$first_run)
{
	$ATplugin->registerFeatures();
}
