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
 * Files exclusion filter based on regular expressions
 */
class OSThumbnailCacheFiles extends FilterBase
{
	function __construct()
	{
		$this->object      = 'file';
		$this->subtype     = 'all';
		$this->method      = 'regex';
		$this->filter_name = 'OSThumbnailCacheFiles';

		parent::__construct();

		// Get the site's root
		$configuration = Factory::getConfiguration();

		$root = $configuration->get('akeeba.platform.newroot', '[SITEROOT]');

		$this->filter_data[$root] = [
			'#/Thumbs.db$#',
			'#^Thumbs.db$#',
			'#/.DS_Store$#i',
			'#^.DS_Store$#i'
		];
	}
}
