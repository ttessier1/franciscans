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
 * Exclude files which we know they should not be backed up
 */
class ExcludeFiles extends FilterBase
{
	public function __construct()
	{
		$this->object      = 'file';
		$this->subtype     = 'all';
		$this->method      = 'direct';
		$this->filter_name = 'ExcludeFiles';

		// Get the site's root
		$configuration = Factory::getConfiguration();

		if ($configuration->get('akeeba.platform.override_root', 0))
		{
			$root = $configuration->get('akeeba.platform.newroot', '[SITEROOT]');
		}
		else
		{
			$root = '[SITEROOT]';
		}

		// We take advantage of the filter class magic to inject our custom filters
		$this->filter_data[$root] = [
			'kickstart.php',
			$this->treatDirectory(APATH_BASE) . '/restoration.php',
		];

		parent::__construct();
	}
}