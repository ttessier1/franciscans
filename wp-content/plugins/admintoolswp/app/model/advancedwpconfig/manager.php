<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Advancedwpconfig;


use Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Features\Base;
use DirectoryIterator;

defined('ADMINTOOLSINC') or die();

class Manager
{
	/** @var self  Singleton holder */
	private static $instance;

	/** @var Base[]  Holder for all WordPress features */
	private $features = [];

	/**
	 * Singleton implementation
	 *
	 * @param array $config Configuration values saved inside the database
	 *
	 * @return Manager
	 */
	public static function getInstance(array $config)
	{
		if (is_null(static::$instance))
		{
			static::$instance = new self($config);
		}

		return static::$instance;
	}

	/**
	 * Manager constructor.
	 *
	 * @param array $config     Configuration values saved inside the database
	 */
	public function __construct(array $config)
	{
		// Load and attach all features
		$di = new DirectoryIterator(__DIR__ . '/features');

		foreach ($di as $fileSpec)
		{
			if ($fileSpec->isDir())
			{
				continue;
			}

			// Get the filename minus the .php extension
			$fileName = $fileSpec->getFilename();
			$fileName = substr($fileName, 0, -4);

			if (in_array($fileName, ['base']))
			{
				continue;
			}

			$className = 'Akeeba\\AdminTools\\Admin\\Model\\Advancedwpconfig\\Features\\' . ucfirst($fileName);

			if (!class_exists($className, true))
			{
				continue;
			}

			/** @var Base $o */
			$o = new $className();

			$this->features[$o->getConfigKey()] = $o;
		}

		$this->bind($config);
	}

	/**
	 * Binds saved configuration values to internal feature objects
	 *
	 * @param array $config
	 */
	public function bind(array $config)
	{
		foreach ($config as $key => $value)
		{
			if (!isset($this->features[$key]))
			{
				continue;
			}

			$this->features[$key]->setConfigValue($value);
		}
	}

	public function getConfigValues()
	{
		$values = [];

		foreach ($this->features as $feature)
		{
			$values[$feature->getConfigKey()] = $feature->getConfigValue();
		}

		return $values;
	}

	/**
	 * @return Base[]
	 */
	public function getFeatures()
	{
		return $this->features;
	}
}