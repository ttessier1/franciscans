<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

/**
 * @property   string  $source
 * @property   string  $dest
 * @property   int     $published
 * @property   int     $keepurlparams
 *
 */
class Redirections extends Model
{
	public function __construct($input)
	{
		parent::__construct($input);
		
		$this->table = '#__admintools_redirects';
		$this->pk    = 'id';
	}

	public function save(array $data = array())
	{
		$db = $this->getDbo();

		if (!$data)
		{
			$data = array(
				'id' => $this->input->getInt('id', 0),
				'source' => $this->input->getString('source', ''),
				'dest' => $this->input->getString('dest', ''),
				'keepurlparams' => $this->input->getInt('keepurlparams', 0),
				'published' => $this->input->getInt('published', 0),
			);
		}

		if (!isset($data['id']))
		{
			$data['id'] = '';
		}

		$data = (object) $data;

		if (!$data->source)
		{
			throw new \Exception(Language::_('COM_ADMINTOOLS_ERR_REDIRECTION_NEEDS_SOURCE'));
		}

		if (!$data->dest)
		{
			throw new \Exception(Language::_('COM_ADMINTOOLS_ERR_REDIRECTION_NEEDS_DEST'));
		}

		if (empty($data->published) && ($data->published !== 0))
		{
			$this->published = 0;
		}

		if (!$data->id)
		{
			$db->insertObject($this->table, $data, $this->pk);
		}
		else
		{
			$db->updateObject($this->table, $data, array($this->pk));
		}

		return $data->id;
	}

	public function setRedirectionState($newState)
	{
		$params = Storage::getInstance();

		$params->setValue('urlredirection', $newState ? 1 : 0);
		$params->save();
	}

	public function getRedirectionState()
	{
		$params = Storage::getInstance();

		return $params->getValue('urlredirection', 1);
	}

	public function buildQuery($overrideLimits = false)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
		            ->select(array('*'))
		            ->from($db->qn('#__admintools_redirects'));

		$fltSource = $this->input->getString('source', null);

		if ($fltSource)
		{
			$fltSource = '%' . $fltSource . '%';
			$query->where($db->qn('source') . ' LIKE ' . $db->q($fltSource));
		}

		$fltDest = $this->input->getString('dest', null);

		if ($fltDest)
		{
			$fltDest = '%' . $fltDest . '%';
			$query->where($db->qn('dest') . ' LIKE ' . $db->q($fltDest));
		}

		$fltKeepURLParams = $this->input->getCmd('keepurlparams', null);

		if (is_numeric($fltKeepURLParams) && !is_null($fltKeepURLParams) && $fltKeepURLParams >= 0)
		{
			$query->where($db->qn('keepurlparams') . ' = ' . $db->q($fltKeepURLParams));
		}

		$fltPublished = $this->input->getCmd('published', null);

		if (!is_null($fltPublished) && ($fltPublished !== ''))
		{
			$query->where($db->qn('published') . ' = ' . $db->q($fltPublished));
		}

		if (!$overrideLimits)
		{
			$ordering  = $this->input->getCmd('ordering', '');
			$direction = $this->input->getCmd('order_dir', '');

			if (!in_array($ordering, array('id', 'source', 'dest', 'keepurlparams', 'published')))
			{
				$ordering = 'id';
			}

			if (!in_array($direction, array('asc', 'desc')))
			{
				$direction = 'desc';
			}

			$query->order($db->qn($ordering).' '.$direction);
		}

		return $query;
	}
}
