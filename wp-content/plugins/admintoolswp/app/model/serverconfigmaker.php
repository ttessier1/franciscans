<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\AdminTools\Library\Uri\Uri;

abstract class ServerConfigMaker extends Model
{
	/**
	 * The default configuration of this feature.
	 *
	 * Note that you define an array. It becomes an object in the constructor. We have to do that since PHP doesn't
	 * allow the intitialisation of anonymous objects (like e.g. Javascript) but lets us typecast an array to an object
	 * – just not in the property declaration!
	 *
	 * @var  object
	 */
	public $defaultConfig = [];

	/**
	 * The current configuration of this feature
	 *
	 * @var  object
	 */
	protected $config = null;

	/**
	 * The current configuration of this feature
	 *
	 * @var  object
	 */
	protected $configKey = '';

	/**
	 * The base name of the configuration file being saved by this feature, e.g. ".htaccess". The file is always saved
	 * in the site's root. Any old files under that name are renamed with a .admintools suffix.
	 *
	 * @var string
	 */
	protected $configFileName = '';

	/**
	 * Compile and return the contents of the server configuration file
	 *
	 * @return  string
	 */
	abstract public function makeConfigFile();

	/**
	 * Nukes current server configuration file, removing all custom rules added by Admin Tools
	 */
	abstract public function nuke();

	public function __construct($input)
	{
		parent::__construct($input);

		$myURI = Uri::getInstance();
		$path  = $myURI->getPath();

		$path_parts = explode('/', $path);
		$path_parts = array_slice($path_parts, 0, count($path_parts) - 2);
		$path       = implode('/', $path_parts);

		$myURI->setPath($path);

		// Unset any query parameters
		$myURI->setQuery('');

		$host = $myURI->toString();
		$host = substr($host, strpos($host, '://') + 3);

		$path = trim($path, '/');

		$this->defaultConfig['rewritebase'] = '/';
		$this->defaultConfig['httphost']    = $host;
		$this->defaultConfig['httpshost']   = $host;

		if (!empty($path))
		{
			$this->defaultConfig['rewritebase'] = $path;
		}

		$this->defaultConfig = (object)$this->defaultConfig;
	}

	/**
	 * Loads the feature's configuration from the database
	 *
	 * @return  object
	 */
	public function loadConfiguration()
	{
		if (is_null($this->config))
		{
			$params = Storage::getInstance();
			$savedConfig = $params->getValue($this->configKey, '');

			if (!empty($savedConfig))
			{
				if (function_exists('base64_encode') && function_exists('base64_encode'))
				{
					$savedConfig = base64_decode($savedConfig);
				}

				$savedConfig = json_decode($savedConfig);
			}
			else
			{
				$savedConfig = array();
			}

			$config = $this->defaultConfig;

			if (!empty($savedConfig))
			{
				foreach ($savedConfig as $key => $value)
				{
					$config->$key = $value;
				}
			}

			$this->config = $config;
		}

		return $this->config;
	}

	/**
	 * Save the configuration to the database
	 *
	 * @param   object|array  $data           The data to save
	 * @param   bool          $isConfigInput  True = $data is object. False (default) = $data is an array.
	 */
	public function saveConfiguration($data, $isConfigInput = false)
	{
		$data   = (array) ($data ?: []);
		$config = $this->defaultConfig;

		$ovars = get_object_vars($config);
		$okeys = array_keys($ovars);

		foreach ($data as $key => $value)
		{
			if (in_array($key, $okeys))
			{
				// Clean up array types coming from textareas
				if (in_array($key, array(
					'hoggeragents', 'extypes', 'exdirs',
					'exceptionfiles', 'exceptionfolders', 'exceptiondirs', 'fullaccessdirs',
					'httpsurls'
				))
				)
				{
					$value = $value ?: [];

					if (is_object($value))
					{
						$value = (array) $value;
					}

					if (is_string($value))
					{
						$value = str_replace("\r", "", $value);
						$value = array_map('trim', explode("\n", $value));
						$value = array_filter($value, function ($x) {
							return ($x !== null) && ($x !== '');
						});
					}

					$value = is_array($value) ? $value : [];
				}

				$config->$key = $value;
			}
		}

		// Make sure nobody tried to add the php extension to the list of allowed extension
		$disallowedExtensions = array('php', 'phP', 'pHp', 'pHP', 'Php', 'PhP', 'PHp', 'PHP');

		foreach ($disallowedExtensions as $ext)
		{
			$pos = array_search($ext, $config->extypes);

			if ($pos !== false)
			{
				unset($config->extypes[ $pos ]);
			}

			$pos = array_search($ext, $config->extypes);

			if ($pos !== false)
			{
				unset($config->extypes[ $pos ]);
			}
		}

		// Always cast it as object, as it would happen with json decode
		$this->config = (object) $config;
		$config       = json_encode($config);

		// This keeps JRegistry from hapily corrupting our data :@
		if (function_exists('base64_encode') && function_exists('base64_encode'))
		{
			$config = base64_encode($config);
		}

		$params = Storage::getInstance();

		$params->setValue($this->configKey, $config);
		$params->setValue('quickstart', 1);

		$params->save();
	}

	/**
	 * Make the configuration file and write it to the disk
	 *
	 * @return  bool
	 */
	public function writeConfigFile()
	{
		$htaccessPath = ABSPATH . '/' . $this->configFileName;
		$backupPath   = ABSPATH . '/' . $this->configFileName . '.admintools';

		if (@file_exists($htaccessPath))
		{
			if (!@copy($htaccessPath, $backupPath))
			{
				return false;
			}
		}

		$configFileContents = $this->makeConfigFile();

		/**
		 * Convert CRLF to LF before saving the file. This would work around an issue with Windows browsers using CRLF
		 * line endings in text areas which would then be transferred verbatim to the output file. Most servers don't
		 * mind, but NginX will break hard when it sees the CR in the configuration file.
		 */
		$configFileContents = str_replace("\r\n", "\n", $configFileContents);

		if (!@file_put_contents($htaccessPath, $configFileContents))
		{
			return false;
		}

		return true;
	}

	/**
	 * Escapes a string so that it's a neutral string inside a regular expression.
	 *
	 * @param   string  $str  The string to escape
	 *
	 * @return  string  The escaped string
	 */
	protected function escape_string_for_regex($str)
	{
		//All regex special chars (according to arkani at iol dot pt below):
		// \ ^ . $ | ( ) [ ]
		// * + ? { } , -

		$patterns = array(
			'/\//', '/\^/', '/\./', '/\$/', '/\|/',
			'/\(/', '/\)/', '/\[/', '/\]/', '/\*/', '/\+/',
			'/\?/', '/\{/', '/\}/', '/\,/', '/\-/'
		);

		$replace = array(
			'\/', '\^', '\.', '\$', '\|', '\(', '\)',
			'\[', '\]', '\*', '\+', '\?', '\{', '\}', '\,', '\-'
		);

		return preg_replace($patterns, $replace, $str);
	}
}
