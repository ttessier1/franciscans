<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Features;

defined('ADMINTOOLSINC') or die;

class Emptytrash extends Base
{
	protected $config_key    = 'empty_trash';

	protected $config_value  = 30;

	protected $constant_name = 'EMPTY_TRASH_DAYS';

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