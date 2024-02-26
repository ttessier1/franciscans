<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Scanner\Mixin;


defined('ADMINTOOLSINC') or die;

trait Singleton
{
	/**
	 * Singleton instance
	 *
	 * @var   static
	 */
	protected static $instance = null;

	/**
	 * Singleton implementation.
	 *
	 * @return  static
	 */
	public static function getInstance()
	{
		if (!empty(static::$instance))
		{
			return static::$instance;
		}

		static::$instance = new static();

		return static::$instance;
	}

}