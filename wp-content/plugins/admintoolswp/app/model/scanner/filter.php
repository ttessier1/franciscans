<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Scanner;

use Akeeba\AdminTools\Admin\Model\Scanner\Util\Configuration;
use Akeeba\AdminTools\Admin\Model\Scanner\Util\Filesystem;

defined('ADMINTOOLSINC') or die;

/**
 * Implements directory and file exclusion filters
 */
class Filter
{
	/**
	 * Scanner configuration
	 *
	 * @var   Configuration
	 */
	private $configuration;

	/**
	 * Excluded directories (relative to site's root)
	 *
	 * @var   array
	 */
	private $directoryFilters = [];

	/**
	 * Excluded files (relative to site's root)
	 *
	 * @var   array
	 */
	private $fileFilters = [];

	/**
	 * File extensions to scan
	 *
	 * @var   array
	 */
	private $scanExtensions = [];

	/**
	 * Filter constructor.
	 *
	 * @param   Configuration  $configuration
	 *
	 * @return  void
	 */
	public function __construct(Configuration $configuration)
	{
		$this->configuration = $configuration;
		$this->loadFilters();
	}

	/**
	 * Is this folder explicitly excluded?
	 *
	 * @param   string  $folder
	 *
	 * @return  bool
	 */
	public function isExcludedFolder($folder)
	{
		return in_array(Filesystem::relativePath($folder), $this->directoryFilters);
	}

	/**
	 * Is this file explicitly excluded?
	 *
	 * @param   string  $file
	 *
	 * @return  bool
	 */
	public function isExcludedFile($file)
	{
		return in_array(Filesystem::relativePath($file), $this->fileFilters);
	}

	/**
	 * is the file excluded because of its extension?
	 *
	 * @param   string  $file
	 *
	 * @return  bool
	 */
	public function isExcludedByExtension($file)
	{
		$extension = pathinfo($file, PATHINFO_EXTENSION);

		return !in_array($extension, $this->scanExtensions);
	}

	/**
	 * Load the filters from the scanner configuration
	 *
	 * @return  void
	 */
	private function loadFilters()
	{
		$callback = ['\Akeeba\AdminTools\Admin\Model\Scanner\Util\Filesystem', 'relativePath'];

		$dirFilters             = $this->configuration->get('directoryFilters');
		$this->directoryFilters = array_map($callback, is_array($dirFilters) ? $dirFilters : $this->stringToArray($dirFilters));
		asort($this->directoryFilters);

		$this->addDefaultDirectoryFilters();

		$fileFilters       = $this->configuration->get('fileFilters');
		$this->fileFilters = array_map($callback, is_array($fileFilters) ? $fileFilters : $this->stringToArray($fileFilters));
		asort($this->fileFilters);

		$scanExtensions       = $this->configuration->get('scanExtensions');
		$this->scanExtensions = array_filter(array_map(function ($x) {
			while (!empty($x) && substr($x, 0, 1) == '.')
			{
				$x = substr($x, 1);
			}

			return $x;
		}, is_array($scanExtensions) ? $scanExtensions : $this->stringToArray($scanExtensions)), function ($x) {
			return !empty($x);
		});
		asort($this->scanExtensions);
	}

	/**
	 * Returns an array of unique, non-empty elements from a newline- or comma-separated list
	 *
	 * @param   string  $string  The string to convert
	 *
	 * @return  array
	 */
	private function stringToArray($string)
	{
		// Explode the string by newlines and then by commas
		$entries = array_map(function ($x) {
			return explode(",", $x);
		}, explode("\n", $string));

		// The array is now in the form of [ ['a', 'b'], ['c'] ] -- Convert to flattened form ['a', 'b', 'c']
		$entries = array_reduce($entries, function ($carry, $x) {
			if (empty($x))
			{
				return $carry;
			}

			return array_merge($carry, array_map(function ($item) {
				return trim($item);
			}, $x));
		}, []);

		// Filter out empty elements and return the unique values
		return array_unique(
			array_filter($entries, function ($x) {
				return !empty($x);
			})
		);
	}

	/**
	 * Normalize the path of an excluded file or folder.
	 *
	 * Runs Filesystem::normalizePath and removes the site's root if it's used as the path prefix.
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 */
	private function normalizePath($path)
	{
		return Filesystem::relativePath($path);
	}

	/**
	 * Add default directory filters.
	 *
	 * Prevents our scanner from scanning temporary, log and cache folders.
	 *
	 * @return  void
	 */
	private function addDefaultDirectoryFilters()
	{
		// In WordPress we do not have default folders for temp/log/cache data. This method is still here just to be
		// consistent with Joomla implementation
		$defaultFilters = [];

		$this->directoryFilters = array_unique(array_merge($this->directoryFilters, $defaultFilters));
	}

}