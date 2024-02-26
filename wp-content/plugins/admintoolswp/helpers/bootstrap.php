<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

if (defined('ADMINTOOLSWP_BOOSTRAP_COMPLETE'))
{
	return;
}

define('ADMINTOOLSWP_BOOSTRAP_COMPLETE', 1);
define('ADMINTOOLSWP_TMP', __DIR__ . '/../app/tmp');

require_once ADMINTOOLSWP_PATH . '/app/library/autoloader/autoloader.php';
require_once __DIR__ . '/installer.php';

// Turn on our debug if Wordpress debug mode is activated
if (defined('WP_DEBUG') && WP_DEBUG && !defined('AKEEBADEBUG'))
{
	define('AKEEBADEBUG', true);
}

Akeeba\AdminTools\Library\Autoloader\Autoloader::getInstance()->addMap('Akeeba\AdminTools\Admin\\', [ADMINTOOLSWP_PATH . '/app']);
