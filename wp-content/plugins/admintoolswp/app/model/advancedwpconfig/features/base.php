<?php
/**
 * @package      admintoolswp
 * @copyright    Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license      GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Features;

defined('ADMINTOOLSINC') or die();

abstract class Base
{
	/** @var string     Name of the key stored inside ATWP configuration */
	protected $config_key;

	/** @var mixed      Value stored inside the configuration */
	protected $config_value;

	/** @var string     Name of the constant that will be used inside wp-config */
	protected $constant_name;

	/** @var bool Should this option live inside namespaced section of wp-config? */
	protected $isNamespaced = true;

	public function getConstantName()
	{
		return $this->constant_name;
	}

	public function getConfigKey()
	{
		return $this->config_key;
	}

	public function getConfigValue()
	{
		return $this->config_value;
	}

	public function setConfigValue($value)
	{
		$this->config_value = $value;
	}

	public function isNamespaced()
	{
		return $this->isNamespaced;
	}

	/**
	 * Creates the value that should be actually written inside wp-config.php file
	 * Return NULL to completely nuke the option inside wp-config file
	 *
	 * @return mixed
	 */
	abstract public function getOptionValue();
}