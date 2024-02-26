<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

class ExceptionsFromWAF extends Model
{
	public function __construct(Input $input)
	{
		parent::__construct($input);

		$this->pk    = 'id';
		$this->table = '#__admintools_wafexceptions';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn($this->table));

		$at_url = $this->input->getString('at_url', null);

		if ($at_url)
		{
			$at_url = '%' . $at_url . '%';
			$query->where($db->qn('at_url') . ' LIKE ' . $db->q($at_url));
		}

		$descr = $this->input->getString('descr', null);

		if ($descr)
		{
			$descr = '%' . $descr . '%';
			$query->where($db->qn('descr') . ' LIKE ' . $db->q($descr));
		}

		if (!$overrideLimits)
		{
			$ordering  = $this->input->getCmd('ordering', '');
			$direction = $this->input->getCmd('order_dir', '');

			if (!in_array($ordering, ['id', 'at_url']))
			{
				$ordering = 'id';
			}

			if (!in_array($direction, ['asc', 'desc']))
			{
				$direction = 'desc';
			}

			$query->order($db->qn($ordering).' '.$direction);
		}

		return $query;
	}

	public function save(array $data = [])
	{
		$db = $this->getDbo();

		if (!$data)
		{
			$data = [
				'id'		=> $this->input->getInt('id', 0),
				'at_url' 	=> $this->input->getString('at_url', ''),
				'descr' 	=> $this->input->getString('descr', ''),
				'at_type' 	=> $this->input->getString('at_type', ''),
				'at_param' 	=> $this->input->getString('at_param', ''),
				'at_value' 	=> $this->input->getString('at_value', ''),
				'published'	=> $this->input->getInt('published', 0),
			];
		}

		if (!isset($data[$this->pk]))
		{
			$data[$this->pk] = '';
		}

		$data = (object) $data;

		// Store the URL as relative, it will make our life easier while running the plugin code
		$siteurl = Wordpress::get_option('siteurl');
		$siteurl = str_replace('http://', '', $siteurl);
		$siteurl = str_replace('https://', '', $siteurl);

		$data->at_url = str_replace('http://', '', $data->at_url);
		$data->at_url = str_replace('https://', '', $data->at_url);
		$data->at_url = str_replace($siteurl, '', $data->at_url);
		$data->at_url = trim($data->at_url, '/');

		if (!$data->at_url)
		{
			throw new \RuntimeException(Language::_('COM_ADMINTOOLS_ERR_WAFEXCEPTIONS_URL'));
		}

		if (!in_array($data->at_type, ['regex', 'exact']))
		{
			throw new \RuntimeException(Language::_('COM_ADMINTOOLS_ERR_WAFEXCEPTIONS_TYPE'));
		}

		if (!$data->id)
		{
			$db->insertObject('#__admintools_wafexceptions', $data, $this->pk);
		}
		else
		{
			$db->updateObject('#__admintools_wafexceptions', $data, [$this->pk]);
		}

		return $data->id;
	}
}
