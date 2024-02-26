<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Features;

defined('ADMINTOOLSINC') or die;

class Stylesheetpath extends Base
{
	protected $config_key    = 'stylesheet_path';

	protected $config_value  = '';

	protected $constant_name = 'STYLESHEETPATH';

	/**
	 * @inheritDoc
	 */
	public function getOptionValue()
	{
		if ($this->config_value)
		{
			return "'".$this->config_value."'";
		}

		return null;
	}
}