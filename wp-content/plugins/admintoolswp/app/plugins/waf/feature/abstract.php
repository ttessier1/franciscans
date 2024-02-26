<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Date\Date;
use Akeeba\AdminTools\Library\Input\Input;

class AtsystemFeatureAbstract
{
	/** @var   Params   Component parameters */
	protected $params = null;

	/** @var   Storage   WAF parameters */
	protected $cparams = null;

	/** @var   Input  Application input */
	protected $input = null;

	/** @var   AtsystemUtilExceptionshandler  The security exceptions handler */
	protected $exceptionsHandler = null;

	/** @var   array  The applicable WAF Exceptions which prevent filtering from taking place */
	protected $exceptions = array();

	/** @var   bool   Should I skip filtering (because of whitelisted IPs, WAF Exceptions etc) */
	protected $skipFiltering = false;

	/** @var   Akeeba\AdminTools\Library\Database\Driver  The database driver */
	protected $db = null;

	/** @var   int  The load order of each feature */
	protected $loadOrder = 9999;

	/** @var null|bool Is this a CLI application? */
	protected static $isCLI = null;

	/** @var null|bool Is this an administrator application? */
	protected static $isAdmin = null;

	/** @var   array  Timestamps of the last run of each scheduled task */
	private $timestamps = [];

	/**
	 * Public constructor. Creates the feature class.
	 *
	 * @param \Akeeba\AdminTools\Library\Database\Driver $db                The database driver
	 * @param Params                                     $params            Plugin parameters
	 * @param Storage                                    $componentParams   Component parameters
	 * @param Input                                      $input             Global input object
	 * @param AtsystemUtilExceptionshandler              $exceptionsHandler Security exceptions handler class (or null if the feature is not implemented)
	 * @param array                                      $exceptions        A list of WAF exceptions
	 * @param bool                                       $skipFiltering     Should I skip the filtering?
	 *
	 */
	public function __construct($db, Params &$params, Storage &$componentParams, &$input, &$exceptionsHandler, array &$exceptions, &$skipFiltering)
	{
		$this->db                = $db;
		$this->params            = $params;
		$this->cparams           = $componentParams;
		$this->input             = $input;
		$this->exceptionsHandler = $exceptionsHandler;
		$this->exceptions        = $exceptions;
		$this->skipFiltering     = $skipFiltering;
	}

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return true;
	}

	/**
	 * Returns the load order of this plugin
	 *
	 * @return int
	 */
	public function getLoadOrder()
	{
		return $this->loadOrder;
	}

	/**
	 * Redirects an administrator request back to the home page
	 */
	protected function redirectToHome()
	{
		$url = Wordpress::get_option('siteurl');

		if (!$url)
		{
			Wordpress::get_option('home');
		}

		header('Location: '.$url);
		exit();
	}

	/**
	 * Runs a RegEx match against a string or recursively against an array.
	 * In the case of an array, the first positive match against any level element
	 * of the array returns true and breaks the RegEx matching loop. If you pass
	 * any other data type except an array or string, it returns false.
	 *
	 * @param string    $regex         The regular expressions to feed to preg_match
	 * @param mixed     $array         The array to scan
	 * @param bool      $striptags     Should I strip tags? Default: no
	 * @param callable  $precondition  A callable to precondition each value before preg_match
	 *
	 * @return bool|int
	 */
	protected function match_array($regex, $array, $striptags = false, $precondition = null)
	{
		$result = false;

		if (!is_array($array) && !is_string($array))
		{
			return false;
		}

		if (!is_array($array))
		{
			$v = $striptags ? strip_tags($array) : $array;

			if (!empty($precondition) && is_callable($precondition))
			{
				$v = call_user_func($precondition, $v);
			}

			return preg_match($regex, $v);
		}

		foreach ($array as $key => $value)
		{
			if (!empty($this->exceptions) && in_array($key, $this->exceptions))
			{
				continue;
			}

			if (is_array($value))
			{
				$result = $this->match_array($regex, $value, $striptags, $precondition);

				if ($result)
				{
					break;
				}

				continue;
			}

			$v = $striptags ? strip_tags($value) : $value;

			if (!empty($precondition) && is_callable($precondition))
			{
				$v = call_user_func($precondition, $v);
			}

			$result = preg_match($regex, $v);

			if ($result)
			{
				break;
			}
		}

		return $result;
	}
}
