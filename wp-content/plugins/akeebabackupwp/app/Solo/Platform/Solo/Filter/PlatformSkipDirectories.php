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
 * Subdirectories exclusion filter
 */
class PlatformSkipDirectories extends FilterBase
{
	public function __construct()
	{
		$this->object      = 'dir';
		$this->subtype     = 'children';
		$this->method      = 'direct';
		$this->filter_name = 'PlatformSkipDirectories';

		// We take advantage of the filter class magic to inject our custom filters
		$configuration = Factory::getConfiguration();

		// Get the site's root
		$root = $configuration->get('akeeba.platform.newroot', '[SITEROOT]');

		$this->filter_data[$root] = [
			// Output & temp directory of the application
			$this->treatDirectory($configuration->get('akeeba.basic.output_directory')),
			// Default backup output directory
			$this->treatDirectory(APATH_BASE . '/backups'),
		];

		if (!$configuration->get('akeeba.platform.addsolo', 0))
		{
			$this->filter_data[$root][] = $this->treatDirectory(APATH_BASE);
		}
		else
		{
			$soloRoot                     = APATH_BASE;
			$this->filter_data[$soloRoot] = [
				$this->treatDirectory($configuration->get('akeeba.basic.output_directory'), $soloRoot),
				'backups',
				$this->treatDirectory(APATH_BASE . '/backups', $soloRoot),
			];
		}

		parent::__construct();
	}
}
