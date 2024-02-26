<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Features;

defined('ADMINTOOLSINC') or die;

class Memorylimit extends Base
{
	protected $config_key    = 'memory_limit';

	protected $config_value  = 64;

	protected $constant_name = 'WP_MEMORY_LIMIT';

	/**
	 * @inheritDoc
	 */
	public function getOptionValue()
	{
		if ($this->config_value)
		{
			$limit = ((int)$this->config_value).'M';

			return "'".$limit."'";
		}

		return null;
	}
}