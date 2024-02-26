<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

defined('ADMINTOOLSINC') or die;

/**
 * This helper class will be used to interact with the .htaccess file. Everything is inside our own starting/closing marks;
 * moreover every feature has its own token. Since we are going to interact with the .htaccess file in several places, we
 * need a way to easily turn on/off specific rules without overwriting everything.
 *
 * @package Akeeba\AdminTools\Admin\Helper
 */
class HtaccessManager
{
	private static $instances = [];

	/** @var string     Path to the .htaccess file we want to load */
	private $path;

	/** @var array      Full list of options applied in the .htaccess file */
	private $options = [];

	/** @var string     Starting mark for our section */
	public $startingMark = '# BEGIN AdminTools';

	/** @var string     Ending mark for our section */
	public $endingMark = '# END AdminTools';

	/** @var string     Legacy marker for marking our section, used to convert .htaccess to the new syntax */
	public $legacyStartingMark = '# +-+-+ Admin Tools for WordPress - Start';

	/** @var string     Legacy marker for marking our section, used to convert .htaccess to the new syntax */
	public $legacyEndingMark = '# +-+-+ Admin Tools for WordPress - End';

	/**
	 * Creates an instance class referring to a specific wp-config.php file
	 *
	 * @param   string|null $htaccessFile     Path to the config file to load. If null the default one will be used
	 *
	 * @return  self
	 */
	public static function getInstance($htaccessFile = null)
	{
		// No file or it doesn't exist? Fallback to the default one
		if (!$htaccessFile)
		{
			$htaccessFile = get_home_path() . '.htaccess';

			// If the WordPress .htaccess file is missing ask WordPress to create one.
			if (!file_exists($htaccessFile))
			{
				self::createDefaultWordPressDotHtaccess();
			}
		}

		// Wrong path, halt and catch fire
		if (!file_exists($htaccessFile))
		{
			throw new \RuntimeException(sprintf("File %s does not exist, can't load WordPress .htaccess", $htaccessFile));
		}

		$hash = md5($htaccessFile);

		if (isset(static::$instances[$hash]))
		{
			return static::$instances[$hash];
		}

		static::$instances[$hash] = new self($htaccessFile);

		return static::$instances[$hash];
	}

	/**
	 * Public constructor, automatically loads the content of the .htaccess file passed as argument
	 *
	 * @param   string  $htaccessFile     Path to the .htaccess file
	 */
	public function __construct($htaccessFile)
	{
		$this->path = $htaccessFile;

		$this->load();
	}

	/**
	 * Loads and parses the options included inside the .htaccess file
	 */
	public function load()
	{
		// Sanity checks
		if (!file_exists($this->path))
		{
			throw new \RuntimeException(sprintf("File %s does not exist, can't load WordPress .htaccess", $this->path));
		}

		// Reset the options
		$this->options = [];

		$contents = file_get_contents($this->path);
		$contents = explode("\n", $contents);

		// First of all let's extract only the contents handled by Admin Tools
		$atsection_start = 0;
		$atsection_end   = 0;

		for ($i = 0; $i < count($contents); $i++)
		{
			$line = trim($contents[$i]);

			if ((stripos($line, $this->startingMark) !== false) || (stripos($line, $this->legacyStartingMark) !== false))
			{
				$atsection_start = $i;

				continue;
			}

			if ((stripos($line, $this->endingMark) !== false) || (stripos($line, $this->legacyEndingMark) !== false))
			{
				$atsection_end = $i;

				break;
			}
		}

		// No Admin Tools section, nothing to load
		if (!$atsection_end)
		{
			return;
		}

		$ats_options = array_slice($contents, $atsection_start + 1, ($atsection_end - $atsection_start));
		$current_key = '';

		// Scan all the lines, associating every rule(s) with its token
		foreach ($ats_options as $orig_line)
		{
			$line = trim($orig_line);

			// No key? Let's extract the name of the option we're currently working on
			if (strpos($line, '#') === 0 && !$current_key)
			{
				preg_match('/# \+\+\+(.*?)\+\+\+/', $line, $matches);

				if (isset($matches[1]))
				{
					// Got the token, let's go to the next line
					$current_key = $matches[1];

					continue;
				}
			}

			// I DO have a key? Let's check if the current line contains the ending token
			if (strpos($line, '#') === 0 && $current_key)
			{
				$regex_key = preg_quote($current_key);
				preg_match('/# \-\-\-('.$regex_key.')\-\-\-/', $line, $matches);

				if (isset($matches[1]))
				{
					// We have a hit, let's go to the next line
					$current_key = '';

					continue;
				}
			}

			// mhm... I don't have a key to store? Ignore the line, sorry
			if (!$current_key)
			{
				continue;
			}

			$this->options[$current_key][] = $orig_line;
		}
	}

	/**
	 * Create WordPress' default .htaccess file if none is present.
	 *
	 * @return  void
	 */
	private static function createDefaultWordPressDotHtaccess()
	{
		$htaccessFile = get_home_path() . '.htaccess';

		// Load WP's misc.php which defines the save_mod_rewrite_rules() function.
		if (!function_exists('save_mod_rewrite_rules'))
		{
			$miscPHP = get_home_path() . ' wp-admin/includes/misc.php';

			@include_once $miscPHP;
		}

		// First try doing it The One True WordPress Way.
		if (function_exists('save_mod_rewrite_rules'))
		{
			save_mod_rewrite_rules();
		}

		// If the One True WP Way worked we're done here.
		if (file_exists($htaccessFile))
		{
			return;
		}

		/**
		 * According to my experience, the One True WordPress way might not always work.
		 *
		 * 1. Multisites. The save_mod_rewrite_rules function does nothing there.
		 * 2. The misc.php file cannot be loaded (never run into this but I won't discount the possibility).
		 * 3. WordPress could not write to the .htaccess file or THOUGHT it couldn't so it never tried.
		 *
		 * In this case we have to implement our own solution
		 */
		@touch($htaccessFile);

		if (is_multisite())
		{
			// We must NOT create any default WP rules for multisites.
			return;
		}

		/** @global \WP_Rewrite $wp_rewrite */
		global $wp_rewrite;

		if (!isset($wp_rewrite) || !class_exists('WP_Rewrite') || !($wp_rewrite instanceof \WP_Rewrite))
		{
			return;
		}

		$content = "# BEGIN WordPress\n";
		$content .= $wp_rewrite->mod_rewrite_rules();
		$content = trim($content) . "\n# END WordPress\n";

		@file_put_contents($htaccessFile, $content);

	}

	/**
	 * Stores the value of an option, we will dump the whole array while writing the file
	 *
	 * @param   string              $key        Name of the option to update
	 * @param   string|array|null   $value      Value of the option
	 */
	public function setOption($key, $value)
	{
		if (is_null($value))
		{
			unset($this->options[$key]);

			return;
		}

		// Cast it to array so I can handle it better
		if (!is_array($value))
		{
			$value = [$value];
		}

		$this->options[$key] = $value;
	}

	/**
	 * Updates the .htaccess file with the new options
	 *
	 * @param   bool    $write  Should we write it or return the new contents?
	 *
	 * @return string
	 */
	public function updateFile($write = true)
	{
		$contents = @file_get_contents($this->path);

		if ($contents === false)
        {
            $contents = '';
        }

		$contents = explode("\n", $contents);

		// Remove the initial part regarding Admin Tools settings: those options are always re-written and handled using
		// Admin Tools plugin configuration
		$atsection_end = 0;
		$found         = false;

		foreach ($contents as $line)
		{
			$line = trim($line);

			$atsection_end++;

			if ((stripos($line, $this->endingMark) !== false) || (stripos($line, $this->legacyEndingMark) !== false))
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

		$new_config = $this->startingMark."\n";

		// Dump our options to disk
		foreach ($this->options as $key => $values)
		{
			// Wrap the feature into a token, so I can later parse it
			$new_config .= "\t# +++$key+++ \n";

			foreach ($values as $value)
			{
				$value = trim($value, "\n");

				// Let's break on new lines, so I can nicely indent everything, even if it's a multi-line option
				$rules = explode("\n", $value);

				foreach ($rules as $rule)
				{
					$new_config .= "\t".$rule."\n";
				}
			}

			$new_config .= "\t# ---$key--- \n\n";
		}

		$new_config .= $this->endingMark."\n";

		// Then write the rest of the original configuration
		foreach ($contents as $line)
		{
			$line = trim($line);
			$new_config .= $line."\n";
		}

		// Finally write the new contents and reload if from disk
		if ($write)
		{
			return file_put_contents($this->path, $new_config);
		}
		else
		{
			return $new_config;
		}
	}
}
