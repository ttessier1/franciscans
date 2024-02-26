<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Features;

defined('ADMINTOOLSINC') or die;

class Crontimeout extends Base
{
	protected $config_key    = 'cron_timeout';

	protected $config_value  = 0;

	protected $constant_name = 'WP_CRON_LOCK_TIMEOUT';

	/**
	 * @inheritDoc
	 */
	public function getOptionValue()
	{
		if ($this->config_value)
		{
			return (int)$this->config_value;
		}

		return null;
	}
}