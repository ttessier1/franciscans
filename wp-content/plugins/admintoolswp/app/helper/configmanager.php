<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

defined('ADMINTOOLSINC') or die;

/**
 * This helper class will be used to interact with the wp-config.php file, keeping track of the features set by Admin Tools
 * AND the ones directly set by the user (since they are allowed to edit the file)
 *
 * @package Akeeba\AdminTools\Admin\Helper
 */
class ConfigManager
{
	private static $instances = [];

	/** @var string     Path to the configuration file we want to load */
	private $path;

	/** @var array      Full list of options applied in the configuration file */
	private $options = [];

	/** @var bool       Flag to know if we're in a comment block while parsing the file */
	private $commentBlock = false;

	/** @var string     While parsing the file, keeps track of the section we're currently in */
	private $currentSection = 'core';

	/** @var string     Starting mark for our section */
	private $startingMark = '// +-+-+ Admin Tools for WordPress - Start';

	/** @var string     Ending mark for our section */
	private $endingMark = '// +-+-+ Admin Tools for WordPress - End';

	/**
	 * Creates an instance class referring to a specific wp-config.php file
	 *
	 * @param   string|null $configFile     Path to the config file to load. If null the default one will be used
	 *
	 * @return  self
	 */
	public static function getInstance($configFile = null)
	{
		// No file or it doesn't exist? Fallback to the default one
		if (!$configFile)
		{
			if (!defined('ABSPATH') || !file_exists(ABSPATH.'wp-config.php'))
			{
				// Can't find the default file? Brace yourself!
				throw new \RuntimeException("Can't detected default location for wp-config.php");
			}

			$configFile = ABSPATH.'wp-config.php';
		}

		// Wrong path, halt and catch fire
		if (!file_exists($configFile))
		{
			throw new \RuntimeException(sprintf("File %s does not exist, can't load WordPress configuration", $configFile));
		}

		$hash = md5($configFile);

		if (isset(static::$instances[$hash]))
		{
			return static::$instances[$hash];
		}

		static::$instances[$hash] = new self($configFile);

		return static::$instances[$hash];
	}

	/**
	 * Public constructor, automatically loads the content of the config file passed as argument
	 *
	 * @param   string  $configFile     Path to the configuration file
	 */
	public function __construct($configFile)
	{
		$this->path = $configFile;

		$this->load();
	}

	/**
	 * Loads and parses the options included inside the configuration file
	 */
	public function load()
	{
		// Sanity checks
		if (!file_exists($this->path))
		{
			throw new \RuntimeException(sprintf("File %s does not exist, can't load WordPress configuration", $this->path));
		}

		// Reset the options. We can have "Core" or "AdminTools" defined ones
		$this->options = [
			'core'       => [],
			'admintools' => []
		];

		$contents = file_get_contents($this->path);
		$contents = explode("\n", $contents);

		foreach ($contents as $line)
		{
			$line = trim($line);

			// Skip empty lines
			if (!$line)
			{
				continue;
			}

 			$this->detectSection($line);

			if ($this->isCommented($line))
			{
				continue;
			}

			// Let's parse a define() line
			if (strpos($line, 'define(') !== false)
			{
				preg_match('#define\(\s?["\'](.*?)["\']\,\s?(.*?)\s?\)\s?;#', $line, $matches);

				if (isset($matches[1]))
				{
					$key   = strtoupper($matches[1]);
					$value = trim($matches[2]);

					// Replace literal true/false values with their boolean types
					if (strtolower($value) === "true" || strtolower($value) === "false")
					{
						$value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
					}

					$this->options[$this->currentSection][$key] = $value;
				}
			}
		}
	}

	/**
	 * Stores the value of an option, keeping track if it should be included in our "namespaced" section or in the
	 * general section
	 *
	 * @param   string  $key        Name of the option to update
	 * @param   string  $value      Value of the option
	 * @param   bool    $namespace  Should we apply such option in our "namespaced" section?
	 */
	public function setOption($key, $value, $namespace)
	{
		$section = 'core';

		if ($namespace)
		{
			$section = 'admintools';

			// If the option is already set in the "core" session, let's move it into admintools
			if (isset($this->options['core'][$key]))
			{
				unset($this->options['core'][$key]);
			}
		}

		if (is_null($value))
		{
			unset($this->options[$section][$key]);
		}
		else
		{
			$this->options[$section][$key] = $value;
		}
	}

	/**
	 * Updates the configuration file with the new options
	 *
	 * @param   bool $write Should we write it or return the new contents?
	 *
	 * @return bool|int|string
	 */
	public function updateFile($write = true)
	{
		$contents = file_get_contents($this->path);
		$contents = explode("\n", $contents);

		// Remove PHP opening tag
		array_shift($contents);

		// Remove the initial part regarding Admin Tools settings: those options are always re-written and handled using
		// Admin Tools plugin configuration
		$atsection_end = 0;
		$found         = false;

		foreach ($contents as $line)
		{
			$line = trim($line);

			$atsection_end++;

			if (stripos($line, $this->endingMark) !== false)
			{
				// Ok, found it we're going to slice the original contents array
				$found = true;
				break;
			}
		}

		if ($found)
		{
			$contents = array_slice($contents, $atsection_end);
		}

		$new_config  = "<?php\n";
		$new_config .= $this->writeAdminToolsSection();

		// Then write the rest of the original configuration
		foreach ($contents as $line)
		{
			$line = trim($line);

			if (strpos($line, 'define(') !== false)
			{
				preg_match('#define\(\s?["\'](.*?)["\']\,\s?(.*?)\s?\)\s?;#', $line, $matches);

				if (isset($matches[1]))
				{
					$key   = strtoupper($matches[1]);

					// If I already have the same option in our section or we want to remove that from the core,
					// let's skip the line
					if (isset($this->options['admintools'][$key]) || !isset($this->options['core'][$key]))
					{
						continue;
					}

					// If I'm here, it means that I have to write again the value. It could be the same or it could be updated
					$value = $this->options['core'][$key];

					// Convert bool to strings
					if (is_bool($value))
					{
						$value = $value ? "true" : "false";
					}

					$line = "define('$key', $value);";
				}
			}

			$new_config .= $line."\n";
		}

		// Finally write the new contents and reload it from disk
		if ($write)
		{
			$result = file_put_contents($this->path, $new_config);
			$this->load();

			return $result;
		}

		return $new_config;

	}

	/**
	 * Writes the section handled by Admin Tools, surrounding it with our starting/enging marks
	 *
	 * @return string
	 */
	private function writeAdminToolsSection()
	{
		$contents = '';

		// Empty, there's nothing to do
		if (!$this->options['admintools'])
		{
			return $contents;
		}

		$contents .= $this->startingMark."\n";

		foreach ($this->options['admintools'] as $key => $value)
		{
			// Convert bool to strings
			if (is_bool($value))
			{
				$value = $value ? "true" : "false";
			}

			$contents .= "define('$key', $value);\n";
		}

		$contents .= $this->endingMark."\n";

		return $contents;
	}

	/**
	 * Detects the current section we're in, by inspecting our starting and closing tags
	 *
	 * @param $line
	 */
	private function detectSection($line)
	{
		if ($this->currentSection == 'core')
		{
			if (stripos($line, $this->startingMark) !== false)
			{
				$this->currentSection = 'admintools';

				return;
			}
		}

		if ($this->currentSection == 'admintools')
		{
			if (stripos($line, $this->endingMark) !== false)
			{
				$this->currentSection = 'core';
			}
		}
	}

	/**
	 * Inspects the current line and decides if we should skip it or not
	 *
	 * @param   string  $line
	 *
	 * @return  bool    Should I skip the current line?
	 */
	private function isCommented($line)
	{
		// Check and process comments only if we're not in a comment block section
		if (!$this->commentBlock)
		{
			// Single line comment, skip the line
			if (strpos($line, '//') === 0 || strpos($line, '#') === 0)
			{
				return true;
			}

			// Multi-line comment on one line, simply skip the line
			if (strpos($line, '/*') === 0 && strpos($line, '*/') !== false)
			{
				return true;
			}

			// Multi-line comment spanning multiple lines
			if (strpos($line, '/*') === 0)
			{
				$this->commentBlock = true;

				return true;
			}
		}

		// I was processing a comment block and I found the ending chars? Let's reset the flag and finally continue processing
		if ($this->commentBlock && strpos($line, '*/') !== false)
		{
			$this->commentBlock = false;

			return true;
		}

		// I'm processing a comment block, ignore everything until I get the ending sequence
		if ($this->commentBlock)
		{
			return true;
		}

		// If we're here, it means that we can process the line
		return false;
	}
}
