<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

defined('ADMINTOOLSINC') or die;

/**
 * Internationalisation class for Admin Tools for WordPress
 *
 * Unlike WordPress, it uses plain text .ini files to store languages. This is necessary for two reasons.
 *
 * First, in auto-prepend mode we don't have the WordPress API available to us. Therefore there is no way to load the
 * necessary language files.
 *
 * Second, plain text .ini files are more accessible to users who'd like to to customise the language of the plugin than
 * the very developer-oriented and highly technical GNU GetText toolchain required by .po/.mo files.
 *
 * @package Akeeba\AdminTools\Admin\Helper
 */
class Language
{
	/**
	 * The cache of translation string map
	 *
	 * @var   array
	 */
	protected static $strings = [];

	/** @var   array[callable]  Callables to use to process translation strings after laoding them */
	private static $iniProcessCallbacks = [];

	/**
	 * Passes a string through a sprintf.
	 *
	 * Note that this method can take a mixed number of arguments as for the sprintf function.
	 *
	 * @param string $string The format string.
	 *
	 * @return  string  The translated strings
	 */
	public static function sprintf($string)
	{
		$args  = func_get_args();
		$count = count($args);

		if ($count > 0)
		{
			$args[0] = self::_($string);

			return call_user_func_array('sprintf', $args);
		}

		return '';
	}

	/**
	 * Translate a string
	 *
	 * @param string $key Language key
	 *
	 * @return  string  Translation
	 */
	public static function _($key)
	{
		if (empty(self::$strings))
		{
			self::loadLanguage('en-GB');
			self::loadLanguage();
		}

		$key = strtoupper($key);

		if (array_key_exists($key, self::$strings))
		{
			return self::$strings[$key];
		}

		return $key;
	}

	/**
	 * Loads the language file for a specific language
	 *
	 * @param string  $langCode     The ISO language code, e.g. en-GB, use null for automatic detection
	 * @param string  $pluginName   The name of the application to load translation strings for
	 * @param string  $suffix       The suffix of the language file, by default it's .ini
	 * @param boolean $overwrite    Should I overwrite old language strings?
	 * @param string  $languagePath The base path to the language files (optional)
	 *
	 * @return  void
	 */
	public static function loadLanguage($langCode = null, $pluginName = 'admintoolswp', $suffix = '.ini', $overwrite = true, $languagePath = null)
	{
		if (is_null($langCode))
		{
			$langCode = self::detectLanguage($pluginName, $suffix, $languagePath);
		}

		if (empty($languagePath))
		{
			$languagePath = ADMINTOOLSWP_PATH . '/language';
		}

		$fileNames = [
			// langPath/MyApp/en-GB.ini
			$languagePath . '/' . strtolower($pluginName) . '/' . $langCode . $suffix,
			// langPath/MyApp/en-GB/en-GB.ini
			$languagePath . '/' . strtolower($pluginName) . '/' . $langCode . '/' . $langCode . $suffix,
			// langPath/en-GB.ini
			$languagePath . '/' . $langCode . $suffix,
			// langPath/en-GB/en-GB.ini
			$languagePath . '/' . $langCode . '/' . $langCode . $suffix,
		];

		$filename = null;

		foreach ($fileNames as $file)
		{
			if (@file_exists($file))
			{
				$filename = $file;
				break;
			}
		}

		if (is_null($filename))
		{
			return;
		}

		// Compatibility with Joomla! translation files and Transifex' broken way to conforming to a broken standard.
		$rawText = @file_get_contents($filename);
		$rawText = str_replace('\\"_QQ_\\"', '\"', $rawText);
		$rawText = str_replace('\\"_QQ_"', '\"', $rawText);
		$rawText = str_replace('"_QQ_\\"', '\"', $rawText);
		$rawText = str_replace('"_QQ_"', '\"', $rawText);
		$rawText = str_replace('\\"', '"', $rawText);

		$strings = self::parse_ini_string($rawText);

		if (!empty(static::$iniProcessCallbacks) && !empty($strings))
		{
			foreach (static::$iniProcessCallbacks as $callback)
			{
				$ret = call_user_func($callback, $filename, $strings);

				if ($ret === false)
				{
					return;
				}
				elseif (is_array($ret))
				{
					$strings = $ret;
				}
			}
		}

		if ($overwrite)
		{
			self::$strings = array_merge(self::$strings, $strings);
		}
		else
		{
			self::$strings = array_merge($strings, self::$strings);
		}
	}

	/**
	 * Automatically detect the language preferences from the browser, choosing
	 * the best fit language that exists on our system or falling back to en-GB
	 * when no preferred language exists.
	 *
	 * @param string $pluginName   The application's name to load language strings for
	 * @param string $suffix       The suffix of the language file, by default it's .ini
	 * @param string $languagePath The base path to the language files (optional)
	 *
	 * @return  string  The language code
	 */
	public static function detectLanguage($pluginName = 'admintoolswp', $suffix = '.ini', $languagePath = null)
	{
		if (empty($languagePath))
		{
			$languagePath = ADMINTOOLSWP_PATH . '/language';
		}

		$userLanguages = self::getLanguagesFromHttp();

		if (
			defined('WPINC') &&
			function_exists('apply_filter') &&
			function_exists('is_admin') &&
			function_exists('get_user_locale') &&
			function_exists('get_locale')
		)
		{
			$locale = apply_filters('plugin_locale', is_admin() ? get_user_locale() : get_locale(), $pluginName);

			if (!empty($locale))
			{
				$userLanguages = [self::convertLocaleIntoLanguageArray($locale)];
			}
		}

		if (empty($userLanguages))
		{
			return 'en-GB';
		}

		$baseName = $languagePath . '/' . strtolower($pluginName) . '/';

		if (!@is_dir($baseName))
		{
			$baseName = $languagePath . '/';
		}

		if (!@is_dir($baseName))
		{
			return 'en-GB';
		}

		// Look for classic file layout
		foreach ($userLanguages as $languageStruct)
		{
			// Search for exact language
			$langFilename = $baseName . $languageStruct[0] . $suffix;

			if (!file_exists($langFilename))
			{
				$langFilename = '';

				if (function_exists('glob'))
				{
					$allFiles = glob($baseName . $languageStruct[1] . '-*' . $suffix);

					// Cover both failure cases: false (filesystem error) and empty array (no file found)
					if (!is_array($allFiles) || empty($allFiles))
					{
						continue;
					}

					$langFilename = array_shift($allFiles);
				}
			}

			if (!empty($langFilename) && file_exists($langFilename))
			{
				return basename($langFilename, $suffix);
			}
		}

		// Look for subdirectory layout
		$allFolders = [];

		try
		{
			$di = new \DirectoryIterator($baseName);
		}
		catch (\Exception $e)
		{
			return 'en-GB';
		}

		/** @var \DirectoryIterator $file */
		foreach ($di as $file)
		{
			if ($di->isDot())
			{
				continue;
			}

			if (!$di->isDir())
			{
				continue;
			}

			$allFolders[] = $file->getFilename();
		}

		foreach ($userLanguages as $languageStruct)
		{
			if (array_key_exists($languageStruct[0], $allFolders))
			{
				return $languageStruct[0];
			}

			foreach ($allFolders as $folder)
			{
				if (strpos($folder, $languageStruct[1]) === 0)
				{
					return $folder;
				}
			}
		}

		return 'en-GB';
	}

	/**
	 * Get a list of user languages based on the HTTP Accept-Language header.
	 *
	 * @return array
	 */
	private static function getLanguagesFromHttp()
	{
		// Do we have the Accept-Language HTTP header?
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			return [];
		}

		/**
		 * Get the languages list from the HTTP header and convert it to a preliminary array
		 *
		 * Sample header contents: fr-ch;q=0.3, da, en-us;q=0.8, en;q=0.5, fr;q=0.3
		 */
		$languages = strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
		$languages = str_replace(' ', '', $languages);
		$languages = explode(",", $languages);

		// Sort languages by their weight
		$temp = [];

		foreach ($languages as $lang)
		{
			$parts = explode(';', $lang);

			$q = 1;

			if ((count($parts) > 1) && (substr($parts[1], 0, 2) == 'q='))
			{
				$q = floatval(substr($parts[1], 2));
			}

			$temp[$parts[0]] = $q;
		}

		arsort($temp);
		$languages     = $temp;
		$userLanguages = [];

		foreach ($languages as $language => $weight)
		{
			$userLanguages[] = self::convertLocaleIntoLanguageArray($language);
		}

		if (empty($userLanguages))
		{
			return [];
		}

		return $userLanguages;
	}

	/**
	 * Converts a locale code (e.g. 'en-GB') into an array of language and locale (e.g. ['en', 'en-GB']).
	 *
	 * @param string $locale The locale to convert, e.g. 'en-GB'
	 *
	 * @return array An array of language and locale, e.g. ['en', 'en-GB']
	 */
	private static function convertLocaleIntoLanguageArray($locale)
	{
		$temp_array = [];
		// Full locale (language and country code)
		$temp_array[0] = $locale;
		// Just the language, without the country
		$parts         = explode('-', $locale);
		$temp_array[1] = $parts[0]; // cut out primary language

		// Normalize the full locale as lowercase language, uppercase country code
		if ((strlen($temp_array[0]) == 5) && ((substr($temp_array[0], 2, 1) == '-') || (substr($temp_array[0], 2, 1) == '_')))
		{
			$langLocation  = strtoupper(substr($temp_array[0], 3, 2));
			$temp_array[0] = $temp_array[1] . '-' . $langLocation;
		}

		return $temp_array;
	}

	/**
	 * Parses an INI string without using PHP's INI parsing features.
	 *
	 * This is necessary because of the weird way PHP handles INI features in its native functions and which easily lead
	 * to inability to load language files.
	 *
	 * @param $rawText
	 *
	 * @return array
	 */
	private static function parse_ini_string($rawText)
	{
		$rawText = str_replace("\r", "", $rawText);
		$ini     = explode("\n", $rawText);

		if (empty($ini))
		{
			return [];
		}

		$sections = [];
		$values   = [];
		$result   = [];
		$globals  = [];
		$i        = 0;

		foreach ($ini as $line)
		{
			$line = trim($line);
			$line = str_replace("\t", " ", $line);

			// Comments
			if (!preg_match('/^[a-zA-Z0-9[]/', $line))
			{
				continue;
			}

			// Sections
			if ($line[0] == '[')
			{
				$tmp        = explode(']', $line);
				$sections[] = trim(substr($tmp[0], 1));
				$i++;
				continue;
			}

			// Key-value pair
			$lineParts = explode('=', $line, 2);

			if (count($lineParts) != 2)
			{
				continue;
			}

			$key   = trim($lineParts[0]);
			$value = trim($lineParts[1]);
			unset($lineParts);

			if (strstr($value, ";"))
			{
				$tmp = explode(';', $value);

				if (count($tmp) == 2)
				{
					if ((($value[0] != '"') && ($value[0] != "'")) ||
						preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
						preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value)
					)
					{
						$value = $tmp[0];
					}
				}
				else
				{
					if ($value[0] == '"')
					{
						$value = preg_replace('/^"(.*)".*/', '$1', $value);
					}
					elseif ($value[0] == "'")
					{
						$value = preg_replace("/^'(.*)'.*/", '$1', $value);
					}
					else
					{
						$value = $tmp[0];
					}
				}
			}

			$value = trim($value);
			$value = trim($value, "'\"");

			if ($i == 0)
			{
				if (substr($line, -1, 2) == '[]')
				{
					$globals[$key][] = $value;

					continue;
				}

				$globals[$key] = $value;

				continue;
			}
			else
			{
				if (substr($line, -1, 2) == '[]')
				{
					$values[$i - 1][$key][] = $value;

					continue;
				}

				$values[$i - 1][$key] = $value;
			}
		}

		for ($j = 0; $j < $i; $j++)
		{
			if (!isset($values[$j]))
			{
				continue;
			}

			$result[] = $values[$j];
		}

		return array_merge($result, $globals);
	}

	/**
	 * Does a translation key exist?
	 *
	 * @param string $key The key to check
	 *
	 * @return  boolean
	 */
	public static function hasKey($key)
	{
		if (empty(self::$strings))
		{
			self::loadLanguage('en-GB');
			self::loadLanguage();
		}

		$key = strtoupper($key);

		return array_key_exists($key, self::$strings);
	}
}