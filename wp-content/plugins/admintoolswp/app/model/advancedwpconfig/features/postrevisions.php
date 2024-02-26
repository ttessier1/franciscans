<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Features;

defined('ADMINTOOLSINC') or die;

class Postrevisions extends Base
{
	protected $config_key    = 'post_revisions';

	protected $config_value  = 'default';

	protected $constant_name = 'WP_POST_REVISIONS';

	/**
	 * @inheritDoc
	 */
	public function getOptionValue()
	{
		// Default value, do not touch anything
		if ($this->config_value == 'default')
		{
			return null;
		}

		if (!$this->config_value == 'disabled')
		{
			return false;
		}

		// Custom revision count
		return (int)$this->config_value;
	}
}