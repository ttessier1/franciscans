<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Features;

defined('ADMINTOOLSINC') or die;

class Concatenatescripts extends Base
{
	protected $config_key    = 'js_concat';

	protected $config_value  = true;

	protected $constant_name = 'CONCATENATE_SCRIPTS';

	/**
	 * @inheritDoc
	 */
	public function getOptionValue()
	{
		if (!$this->config_value)
		{
			return false;
		}

		return null;
	}
}