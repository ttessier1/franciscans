<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Filter;

use Akeeba\Engine\Factory;
use Akeeba\Engine\Filter\Base as FilterBase;

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * Database table exclusion filter
 */
class SoloTables extends FilterBase
{
	public function __construct()
	{
		$this->object      = 'dbobject';
		$this->subtype     = 'all';
		$this->method      = 'regex';
		$this->filter_name = 'SoloTables';

		$configuration = Factory::getConfiguration();

		if (!$configuration->get('akeeba.platform.addsolo', 0))
		{
			// Site database connection information
			$siteDbHost   = $configuration->get('akeeba.platform.dbhost', '');
			$siteDbName   = $configuration->get('akeeba.platform.dbname', '');
			$siteDbPrefix = $configuration->get('akeeba.platform.dbprefix', '');

			// Akeeba Solo connection information
			$appConfig    = \Awf\Application\Application::getInstance()->getContainer()->appConfig;
			$soloDbHost   = $appConfig->get('dbhost', '');
			$soloDbName   = $appConfig->get('dbname', '');
			$soloDbPrefix = $appConfig->get('prefix', '');

			// If Solo is installed in the same db as the site...
			if (($soloDbHost == $siteDbHost) && ($soloDbName == $siteDbName))
			{
				// ...check which prefix is being used...
				$soloPrefix = ($siteDbPrefix == $soloDbPrefix) ? '#__' : $soloDbPrefix;

				// ...and exclude Solo's tables with a RegEx
				$this->filter_data['[SITEDB]'] = [
					'/^' . $soloPrefix . 'ak_/',
					'/^' . $soloPrefix . 'akeeba_/',
				];
			}
		}

		parent::__construct();
	}
}
