<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Features;

defined('ADMINTOOLSINC') or die;

class Autosaveinterval extends Base
{
	protected $config_key    = 'autosave_interval';

	protected $config_value  = 60;

	protected $constant_name = 'AUTOSAVE_INTERVAL';

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