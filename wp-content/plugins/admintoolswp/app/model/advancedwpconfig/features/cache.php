<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Features;

defined('ADMINTOOLSINC') or die;

class Cache extends Base
{
	protected $config_key    = 'cache';

	protected $config_value  = false;

	protected $constant_name = 'WP_CACHE';

	/**
	 * @inheritDoc
	 */
	public function getOptionValue()
	{
		if ($this->config_value)
		{
			return true;
		}

		return null;
	}
}