<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Library\Filesystem\File;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\AdminTools\Library\Utils\Path;

class FixPermissions extends Model
{
	/** @var float The time the process started */
	private $startTime = null;

	/** @var array The folders to process */
	private $folderStack = array();

	/** @var array The files to process */
	private $filesStack = array();

	/** @var int Total numbers of folders in this site */
	public $totalFolders = 0;

	/** @var int Numbers of folders already processed */
	public $doneFolders = 0;

	/** @var int Default directory permissions */
	private $dirperms = 0755;

	/** @var int Default file permissions */
	private $fileperms = 0644;

	/** @var array Custom permissions */
	private $customperms = array();

	/** @var array Skip subdirectories and files of these directories */
	private $skipDirs = array();

	public function __construct($input)
	{
		parent::__construct($input);

		$params = Storage::getInstance();

		$dirperms  = '0' . ltrim(trim($params->getValue('dirperms', '0755')), '0');
		$fileperms = '0' . ltrim(trim($params->getValue('fileperms', '0644')), '0');

		$dirperms = octdec($dirperms);
		if (($dirperms < 0400) || ($dirperms > 0777))
		{
			$dirperms = 0755;
		}
		$this->dirperms = $dirperms;

		$fileperms = octdec($fileperms);
		if (($fileperms < 0400) || ($fileperms > 0777))
		{
			$fileperms = 0755;
		}
		$this->fileperms = $fileperms;

		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select(array(
				$db->qn('path'),
				$db->qn('perms')
			))->from($db->qn('#__admintools_customperms'))
			->order($db->qn('path') . ' ASC');

		$this->customperms = $db->setQuery($query)->loadAssocList('path');
	}

	/**
	 * Returns the current timestampt in decimal seconds
	 */
	private function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());

		return ((float)$usec + (float)$sec);
	}

	/**
	 * Starts or resets the internal timer
	 */
	private function resetTimer()
	{
		$this->startTime = $this->microtime_float();
	}

	/**
	 * Makes sure that no more than 3 seconds since the start of the timer have
	 * elapsed
	 *
	 * @return bool
	 */
	private function haveEnoughTime()
	{
		$now = $this->microtime_float();
		$elapsed = abs($now - $this->startTime);

		return $elapsed < 2;
	}

	/**
	 * Saves the file/folder stack in the session
	 */
	private function saveStack()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->delete($db->qn('#__admintools_storage'))
			->where($db->qn('at_key') . ' = ' . $db->q('fixperms_stack'));

		$db->setQuery($query)->execute();

		$object = (object)array(
			'at_key'   => 'fixperms_stack',
			'at_value' => json_encode(array(
				'folders' => $this->folderStack,
				'files'   => $this->filesStack,
				'total'   => $this->totalFolders,
				'done'    => $this->doneFolders
			))
		);

		$db->insertObject('#__admintools_storage', $object);
	}

	/**
	 * Resets the file/folder stack saved in the session
	 */
	private function resetStack()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->delete($db->qn('#__admintools_storage'))
			->where($db->qn('at_key') . ' = ' . $db->q('fixperms_stack'));

		$db->setQuery($query)->execute();

		$this->folderStack  = array();
		$this->filesStack   = array();
		$this->totalFolders = 0;
		$this->doneFolders  = 0;
	}

	/**
	 * Loads the file/folder stack from the session
	 */
	private function loadStack()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select(array($db->qn('at_value')))
			->from($db->qn('#__admintools_storage'))
			->where($db->qn('at_key') . ' = ' . $db->q('fixperms_stack'));

		$stack = $db->setQuery($query)->loadResult();

		if (empty($stack))
		{
			$this->folderStack = array();
			$this->filesStack = array();
			$this->totalFolders = 0;
			$this->doneFolders = 0;

			return;
		}

		$stack = json_decode($stack, true);

		$this->folderStack  = $stack['folders'];
		$this->filesStack   = $stack['files'];
		$this->totalFolders = $stack['total'];
		$this->doneFolders  = $stack['done'];
	}

	/**
	 * Scans $root for directories and updates $folderStack
	 *
	 * @param string $root The full path of the directory to scan
	 */
	public function getDirectories($root = null)
	{
		if (empty($root))
		{
			$root = ABSPATH;
		}

		if (in_array(rtrim($root, '/'), $this->skipDirs))
		{
			return;
		}

		$folders	= array();
		$filesystem = new File();
		$tmpFolders = $filesystem->listFolders($root);

		// Iterate over the folders to build the full path
		foreach ($tmpFolders as $tmpFolder)
		{
			$folders[] = ABSPATH . $tmpFolder;
		}

		$this->totalFolders += count($folders);

		if (!empty($folders))
		{
			$this->folderStack = array_merge($this->folderStack, $folders);
		}
	}

	/**
	 * Scans $root for files and updates $filesStack
	 *
	 * @param string $root The full path of the directory to scan
	 */
	public function getFiles($root = null)
	{
		if (empty($root))
		{
			$root = ABSPATH;
		}

		if (empty($root))
		{
			$root = '..';
			$root = realpath($root);
		}

		if (in_array(rtrim($root, '/'), $this->skipDirs))
		{
			return;
		}

		$root = rtrim($root, '/') . '/';

		// Should I include dot files, too?
		$params = Storage::getInstance();

		$excludeFilter = $params->getValue('perms_show_hidden', 0) ? array('.*~') : array('^\..*', '.*~');

		$filesystem = new File();
		$folders = $filesystem->directoryFiles($root, '.', false, true, array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludeFilter);
		$this->filesStack = array_merge($this->filesStack, $folders);

		$this->totalFolders += count($folders);
	}

	public function startScanning()
	{
		$this->resetStack();
		$this->resetTimer();
		$this->getDirectories();
		$this->getFiles();
		$this->saveStack();

		if (!$this->haveEnoughTime())
		{
			return true;
		}
		else
		{
			return $this->run(false);
		}
	}

	public function chmod($path, $mode)
	{
		// TODO We're going to add compatibility with the FTP layer
		if (is_string($mode))
		{
			$mode = octdec($mode);

			if (($mode <= 0) || ($mode > 0777))
			{
				$mode = 0755;
			}
		}

		// Check to make sure the path valid and clean
		$path = Path::clean($path);

		return @chmod($path, $mode);
	}

	public function run($resetTimer = true)
	{
		if ($resetTimer)
		{
			$this->resetTimer();
		}

		$this->loadStack();

		$result = true;

		while ($result && $this->haveEnoughTime())
		{
			$result = $this->RealRun();
		}

		$this->saveStack();

		return $result;
	}

	private function RealRun()
	{
		while (empty($this->filesStack) && !empty($this->folderStack))
		{
			// Get a directory
			$dir = null;

			while (empty($dir) && !empty($this->folderStack))
			{
				// Get the next directory
				$dir = array_shift($this->folderStack);

				// Skip over non-directories and symlinks
				if (!@is_dir($dir) || @is_link($dir))
				{
					$dir = null;
					continue;
				}
				// Skip over . and ..
				$checkDir = str_replace('\\', '/', $dir);

				if (in_array(basename($checkDir), array('.', '..')) || (substr($checkDir, -2) == '/.') || (substr($checkDir, -3) == '/..'))
				{
					$dir = null;
					continue;
				}

				// Check for custom permissions
				$reldir = $this->getRelativePath($dir);

				if (array_key_exists($reldir, $this->customperms))
				{
					$perms = $this->customperms[$reldir]['perms'];
				}
				else
				{
					$perms = $this->dirperms;
				}

				// Apply new permissions
				$this->chmod($dir, $perms);
				$this->doneFolders++;
				$this->getDirectories($dir);
				$this->getFiles($dir);

				if (!$this->haveEnoughTime())
				{
					// Gotta continue in the next step
					return true;
				}
			}
		}

		if (empty($this->filesStack) && empty($this->folderStack))
		{
			// Just finished
			$this->resetStack();

			return false;
		}

		if (!empty($this->filesStack) && $this->haveEnoughTime())
		{
			while (!empty($this->filesStack))
			{
				$file = array_shift($this->filesStack);

				// Skip over symlinks and non-files
				if (@is_link($file) || !@is_file($file))
				{
					continue;
				}

				$reldir = $this->getRelativePath($file);

				if (array_key_exists($reldir, $this->customperms))
				{
					$perms = $this->customperms[$reldir]['perms'];
				}
				else
				{
					$perms = $this->fileperms;
				}

				$this->chmod($file, $perms);
				$this->doneFolders++;
			}
		}

		if (empty($this->filesStack) && empty($this->folderStack))
		{
			// Just finished
			$this->resetStack();

			return false;
		}

		return true;
	}

	public function getRelativePath($somepath)
	{
		$path = Path::clean($somepath, '/');

		// Clean up the root
		$root = Path::clean(ABSPATH, '/');

		// Find the relative path and get the custom permissions
		$relpath = ltrim(substr($path, strlen($root)), '/');

		return $relpath;
	}
}
