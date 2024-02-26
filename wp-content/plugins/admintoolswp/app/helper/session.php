<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Library\Registry\Registry;

/**
 * Creates a fake session using user meta options. Session data will be automatically deleted
 * once the user performs the logout or his session cookie expires
 */
class Session
{
	/** @var  Registry */
	private static $cache;

	/**
	 * Stores a value inside Admin Tools session-like storage
	 *
	 * @param   string  $key
	 * @param   string  $value
	 *
	 * @return bool
	 */
	public static function set($key, $value)
	{
		if (!self::$cache)
		{
			self::load();
		}

		self::$cache->set($key, $value);

		if (!function_exists('get_current_user_id'))
		{
			return false;
		}

		return update_user_meta(get_current_user_id(), 'admintoolswp_storage', self::$cache->toString());
	}

	/**
	 * Get a value from Admin Tools session-like storage
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 *
	 * @return mixed
	 */
	public static function get($key, $default = null)
	{
		if (!self::$cache)
		{
			self::load();
		}

		return self::$cache->get($key, $default);
	}

	public static function dumpData()
	{
		return self::$cache->toString('JSON');
	}

	public static function setData($json)
	{
		self::$cache = new Registry($json);
	}

	/**
	 * Load the data from the db into the cache
	 */
	private static function load()
	{
		$data = [];

		if (function_exists('get_current_user_id'))
		{
			$data = get_user_meta(get_current_user_id(), 'admintoolswp_storage', true);
		}

		self::$cache = new Registry($data);
	}
}
