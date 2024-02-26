<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\Params as PluginParams;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class Params extends Model
{
	/**
	 * Default configuration variables
	 *
	 * @var array
	 */
	private $defaultConfig = [
		'scandiffs'                                => 0,
		'scanemail'                                => '',
		'scan_conditional_email'                   => 1,
		'email_timezone'                           => 'UTC',
		'showstats'                                => 1,
		'detected_exceptions_from_private_network' => 0,
		'frontend_enable'                          => 0,
		'frontend_secret_word'                     => '',
		'maxlogentries'                            => 0,
		'downloadid'                               => '',
		'minstability'                             => 'stable',
		'stats_enabled'                            => 1,
		'longConfig'                               => 0,
		'darkmode'                                 => -1,
		'logLevel'                                 => 4,
		'minExec'                                  => 2,
		'maxExec'                                  => 5,
		'runtimeBias'                              => 75,
		'dirThreshold'                             => 50,
		'fileThreshold'                            => 100,
		'directoryFilters'                         => '',
		'fileFilters'                              => '',
		'scanExtensions'                           => 'php, phps, phtml, php3, inc',
		'largeFileThreshold'                       => 524288,
		'scanignorenonthreats'                     => 0,
		'oversizeFileThreshold'                    => 5242880,
	];

	/**
	 * Load the Plugin configuration
	 *
	 * @return  array
	 */
	public function getItems($overrideLimits = false, $limitstart = 0, $limit = 0)
	{
		$params = PluginParams::getInstance();

		$config = [];

		foreach ($this->defaultConfig as $k => $v)
		{
			$config[$k] = $params->getValue($k, $v);
		}

		return $config;
	}

	/**
	 * Merge and save $newParams into the Plugin configuration
	 *
	 * @param   array  $newParams  New parameters to save
	 *
	 * @return  void
	 */
	public function saveConfig(array $newParams)
	{
		$params = PluginParams::getInstance();

		foreach ($newParams as $key => $value)
		{
			// Do not save unnecessary parameters
			if (!array_key_exists($key, $this->defaultConfig))
			{
				continue;
			}

			$params->setValue($key, $value);
		}

		$params->save();
	}
}
