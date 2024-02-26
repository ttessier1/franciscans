<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Session;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Library\Filesystem\File;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\AdminTools\Library\Utils\Path;

/**
 * Class ConfigureFixPermissions
 *
 * @property   string  $path
 * @property   string  $perms
 *
 */
class ConfigureFixPermissions extends Model
{
	/** @var array */
	public $list;

	public function __construct(Input $input)
	{
		parent::__construct($input);

		$this->table = '#__admintools_customperms';
		$this->pk    = 'id';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_customperms'));

		$fltPath = $this->input->getString('filter_path', Session::get('configurefixperm_filter_path', ''));

		if ($fltPath)
		{
			$fltPath = $fltPath . '%';
			$query->where($db->qn('path') . ' LIKE ' . $db->q($fltPath));
		}

		$fltPerms = $this->input->getCmd('perms', null);

		if ($fltPerms)
		{
			$query->where($db->qn('perms') . ' = ' . $db->q($fltPerms));
		}

		return $query;
	}

	public function saveDefaults()
	{
		$dirperms  = $this->input->getCmd('dirperms', '0755');
		$fileperms = $this->input->getCmd('fileperms', '0644');

		$dirperms = octdec($dirperms);

		if (($dirperms < 0600) || ($dirperms > 0777))
		{
			$dirperms = 0755;
		}

		$fileperms = octdec($fileperms);
		if (($fileperms < 0600) || ($fileperms > 0777))
		{
			$fileperms = 0755;
		}

		$params = Storage::getInstance();

		$params->setValue('dirperms', '0' . decoct($dirperms));
		$params->setValue('fileperms', '0' . decoct($fileperms));
		$params->setValue('perms_show_hidden', $this->input->getInt('perms_show_hidden', 0));

		$params->save();
	}

	public function applyPath()
	{
		// Get and clean up the path
		$path    = $this->input->get('path', '', 'raw');
		$relpath = $this->getRelativePath($path);

		Session::set('configurefixperm_filter_path', $relpath);

		$this->list = $this->getItems(true, 0, 0);
	}

	public function getRelativePath($somepath)
	{
		$path = ABSPATH . '/' . $somepath;
		$path = Path::clean($path, '/');

		// Clean up the root
		$root = Path::clean(ABSPATH, '/');

		// Find the relative path and get the custom permissions
		$relpath = ltrim(substr($path, strlen($root)), '/');

		return $relpath;
	}

	public function getListing()
	{
		$filesystem = new File();
		$this->applyPath();

		$relpath = Session::get('configurefixperm_filter_path', '');
		$path = ABSPATH . $relpath;

		$folders_raw = $filesystem->listFolders($path);

		$params = Storage::getInstance();

		$excludeFilter = $params->getValue('perms_show_hidden', 0) ? array('.*~') : array('^\..*', '.*~');
		$files_raw     = $filesystem->directoryFiles($path, '.', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludeFilter);

		if (!empty($relpath))
		{
			$relpath .= '/';
		}

		$folders = array();

		if (!empty($folders_raw))
		{
			foreach ($folders_raw as $folder)
			{
				$perms        = $this->getPerms($relpath . $folder);
				$currentperms = @fileperms(ABSPATH . $relpath . $folder);
				$owneruser    = function_exists('fileowner') ? fileowner(ABSPATH . $relpath . $folder) : false;
				$ownergroup   = function_exists('filegroup') ? filegroup(ABSPATH . $relpath . $folder) : false;

				$folders[] = array(
					'item'      => $folder,
					'path'      => $relpath . $folder,
					'perms'     => $perms,
					'realperms' => $currentperms,
					'uid'       => $owneruser,
					'gid'       => $ownergroup
				);
			}
		}

		$files = array();

		if (!empty($files_raw))
		{
			foreach ($files_raw as $file)
			{
				$perms        = $this->getPerms($relpath . $file);
				$currentperms = @fileperms(ABSPATH . $relpath . $file);
				$owneruser    = function_exists('fileowner') ? @fileowner(ABSPATH . $relpath . $file) : false;
				$ownergroup   = function_exists('filegroup') ? @filegroup(ABSPATH . $relpath . $file) : false;

				$files[] = array(
					'item'      => $file,
					'path'      => $relpath . $file,
					'perms'     => $perms,
					'realperms' => $currentperms,
					'uid'       => $owneruser,
					'gid'       => $ownergroup
				);
			}
		}

		$crumbs = explode('/', $relpath);

		return array('folders' => $folders, 'files' => $files, 'crumbs' => $crumbs);
	}

	public function getPerms($path)
	{
		if (count($this->list))
		{
			foreach ($this->list as $item)
			{
				if ($item->path == $path)
				{
					return $item->perms;
				}
			}
		}
		return '';
	}

	public function savePermissions($apply = false)
	{
		if ($apply)
		{
			/** @var FixPermissions $fixmodel */
			$fixmodel = new FixPermissions($this->input);
		}

		$db 	 = $this->getDbo();
		$relpath = Session::get('configurefixperm_filter_path', '');

		if (!empty($relpath))
		{
			$path_esc = $db->escape($relpath);
			$query = $db->getQuery(true)
				->delete($db->qn('#__admintools_customperms'))
				->where(
					$db->qn('path') . ' REGEXP ' .
					$db->q('^' . $path_esc . '/[^/]*$')
				);

			$db->setQuery($query)->execute();
		}

		$folders = $this->input->get('folders', array(), 'array');

		if (!empty($folders))
		{
			if (empty($relpath))
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_customperms'));

				$sqlparts = array();

				foreach ($folders as $folder => $perms)
				{
					$sqlparts[] = $db->q($folder);
				}

				$query->where($db->qn('path') . ' IN (' . implode(', ', $sqlparts) . ')');

				$db->setQuery($query)->execute();
			}

			$sqlparts = array();

			foreach ($folders as $folder => $perms)
			{
				if (!empty($perms))
				{
					$sqlparts[] = $db->q($folder) . ', ' . $db->q($perms);

					if ($apply)
					{
						$fixmodel->chmod(ABSPATH . $folder, $perms);
					}
				}
			}

			if (!empty($sqlparts))
			{
				$query = $db->getQuery(true)
					->insert($db->qn('#__admintools_customperms'))
					->columns(array(
						$db->qn('path'),
						$db->qn('perms')
					))->values($sqlparts);

				$db->setQuery($query)->execute();
			}
		}

		$files = $this->input->get('files', array(), 'array');

		if (!empty($files))
		{
			if (empty($relpath))
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_customperms'));

				$sqlparts = array();

				foreach ($files as $file => $perms)
				{
					$sqlparts[] = $db->q($file);
				}

				$query->where($db->qn('path') . ' IN (' . implode(', ', $sqlparts) . ')');

				$db->setQuery($query)->execute();
			}

			$sqlparts = array();

			foreach ($files as $file => $perms)
			{
				if (!empty($perms))
				{
					$sqlparts[] = $db->q($file) . ', ' . $db->q($perms);

					if ($apply)
					{
						$fixmodel->chmod(ABSPATH . $file, $perms);
					}
				}
			}

			if (!empty($sqlparts))
			{
				$query = $db->getQuery(true)
					->insert($db->qn('#__admintools_customperms'))
					->columns(array(
						$db->qn('path'),
						$db->qn('perms')
					))->values($sqlparts);

				$db->setQuery($query)->execute();
			}
		}
	}
}
