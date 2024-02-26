<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\ConfigManager;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\AdminTools\Library\Uri\Uri;

class HttpsTools extends Model
{
	public $defaultConfig = array(
		'linkmigration' => 0,
		'migratelist'   => '',
		'httpsizer'     => 0,
		'forcewphttps'  => 0,
	);

	public function getConfig()
	{
		$params = Storage::getInstance();
		
		$config = array();
		
		foreach ($this->defaultConfig as $k => $v)
		{
			$config[$k] = $params->getValue($k, $v);
		}

		return $config;
	}

	public function saveConfig($newParams)
	{
		$params = Storage::getInstance();

		foreach ($newParams as $key => $value)
		{
			$params->setValue($key, $value);
		}

		$params->save();

		$force = $params->getValue('forcewphttps', 0);

		// Update the options only if we really want to
		if ($force != -1)
		{
			$this->applySslWordpress($force);
		}
	}

	/**
	 * Automatically applies the required changes to WordPress CMS in order to use SSL on the whole site
	 *
	 * @param   int    $ssl    Should I apply the HTTPS or HTTP protocol?
	 */
	public function applySslWordpress($ssl = 1)
	{
		// Cast it as integer, since we store it as string
		$ssl = (int) $ssl;

		// First of all let's update the URLs
		$siteurl = Wordpress::get_option('siteurl');
		$home    = Wordpress::get_option('home');

		// Let's remove the protocol
		$siteurl = Uri::getInstance($siteurl)->toString(array('host', 'path'));
		$home    = Uri::getInstance($home)->toString(array('host', 'path'));

		// Apply the new protocol
		$siteurl = $ssl ? 'https://'.$siteurl : 'http://'.$siteurl;
		$home    = $ssl ? 'https://'.$home : 'http://'.$home;

		// Save back the results
		update_option('siteurl', $siteurl);
		update_option('home', $home);

		$force_option = $ssl ? true : null;

		$configManager = ConfigManager::getInstance();
		$configManager->setOption('FORCE_SSL_ADMIN', $force_option, true);
		$configManager->updateFile();
	}
}
