<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model\Scanner\Util;

use Akeeba\AdminTools\Admin\Helper\Session as SessionWP;
use Akeeba\AdminTools\Admin\Model\Scanner\Mixin\Singleton;

defined('ADMINTOOLSINC') or die;

/**
 * Temporary session data management.
 *
 * This is used to manage the persistence of temporary information between consecutive steps of the file change scanner
 * engine in the session. During CLI execution the pseudo-session of FOF 3 is used instead.
 */
class Session
{
	use Singleton;

	/**
	 * Known temporary variable keys. Used for reset().
	 *
	 * @var   array
	 */
	private $knownKeys = [
		// Position of the DirectoryIterator when scanning subfolders
		'dirPosition',
		// Position of the DirectoryIterator when scanning files
		'filePosition',
		// Step break flag
		'breakFlag',
		// Files already scanned
		'scannedFiles',
		// ID of this scan
		'scanID',
		// Previously completed step number
		'step',
		// Directories to scan
		'directoryQueue',
		// Files to scan
		'fileQueue',
		// Have I finished listing files in the current directory?
		'hasScannedFiles',
		// Have I finished listing folders in the current directory?
		'hasScannedFolders',
		// Current directory being processed
		'currentDirectory',
		// Current state of the Crawler engine
		'crawlerState',
	];

	/**
	 * Get the value of a temporary variable
	 *
	 * @param   string      $key      The temporary variable to retrieve
	 * @param   null|mixed  $default  Default value to return if the variable is not set
	 *
	 * @return  mixed
	 */
	public function get($key, $default = null)
	{
		return SessionWP::get('filescanner.' . $key, $default);
	}

	/**
	 * Set the value of a temporary variable
	 *
	 * @param   string  $key    The temporary variable to set
	 * @param   mixed   $value  The value to set it to
	 *
	 * @return  void
	 */
	public function set($key, $value)
	{
		if (!in_array($key, $this->knownKeys))
		{
			$this->knownKeys[] = $key;
		}

		SessionWP::set('filescanner.' . $key, $value);
	}

	/**
	 * Remove (unset) a temporary variable
	 *
	 * @param   string  $key  The variable to unset
	 *
	 * @return  void
	 */
	public function remove($key)
	{
		SessionWP::set('filescanner.' . $key, null);
	}

	/**
	 * Remove all temporary variables from the session.
	 *
	 * IMPORTANT! This only removes the variables in $knownKeys unless you pass it a list of key names to reset. In the
	 * latter case BOTH the known keys AND the $resetKeys will be reset.
	 *
	 * @param   array  $resetKeys  Optional. Additional keys to reset beyond $knownKeys
	 */
	public function reset(array $resetKeys = [])
	{
		foreach (array_unique(array_merge($this->knownKeys, $resetKeys)) as $key)
		{
			$this->remove($key);
		}
	}

	public function getKnownKeys()
	{
		return $this->knownKeys;
	}
}