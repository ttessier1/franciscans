<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

use Akeeba\AdminTools\Library\Registry\Registry;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

defined('ADMINTOOLSINC') or die();

/**
 * A helper class to handle the storage of plugin configuration values in the database
 */
class Params
{
	/** @var  Registry  The internal values registry */
	private $config = null;

	/** @var  Params  Singleton instance */
	private static $instance = null;

	/**
	 * Singleton implementation
	 *
	 * @return \Akeeba\AdminTools\Admin\Helper\Params
	 *
	 *
	 */
	public static function &getInstance()
	{
		if (is_null(static::$instance))
		{
			static::$instance = new Params();
		}

		return static::$instance;
	}

	/**
	 * Storage constructor.
	 */
	public function __construct()
	{
		$this->load();
	}

	/**
	 * Retrieve a value
	 *
	 * @param   string  $key      The key to retrieve
	 * @param   mixed   $default  Default value if the key is not set
	 *
	 * @return  mixed  The key's value (or the default value)
	 */
	public function getValue($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * Set a configuration value
	 *
	 * @param   string  $key    Key to set
	 * @param   mixed   $value  Value to set the key to
	 * @param   bool    $save   Should I save everything to database?
	 *
	 * @return  mixed  The old value of the key
	 */
	public function setValue($key, $value, $save = false)
	{
		$x = $this->config->set($key, $value);

		if ($save)
		{
			$this->save();
		}

		return $x;
	}

	/**
	 * Resets the storage
	 *
	 * @param   bool  $save  Should I save everything to database?
	 */
	public function resetContents($save = false)
	{
		$this->config->loadArray(array());

		if ($save)
		{
			$this->save();
		}
	}

	/**
	 * Load the configuration information from the database
	 *
	 * @return  void
	 */
	public function load()
	{
		$data = '';

		$db = Wordpress::getDb();
		$query = $db->getQuery(true)
					->select($db->qn('at_value'))
			        ->from($db->qn('#__admintools_storage'))
			        ->where($db->qn('at_key').' = '.$db->q('pparams'));

		try
		{
			$data = $db->setQuery($query)->loadResult();
		}
		catch (\RuntimeException $e)
		{

		}

		$this->config = new Registry($data);
	}

	/**
	 * Save the configuration information to the database
	 *
	 * @return  void
	 */
	public function save()
	{
		$data = $this->config->toArray();

		$data = json_encode($data);

		$db = Wordpress::getDb();
		$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_storage'))
					->where($db->qn('at_key').' = '.$db->q('pparams'));

		$db->setQuery($query)->execute();

		$object = (object) array(
			'at_key'   => 'pparams',
			'at_value' => $data
		);

		$db->insertObject('#__admintools_storage', $object);
	}
}
