<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class WAFEmailTemplates extends Model
{
	public function __construct(Input $input)
	{
		parent::__construct($input);

		$this->pk    = 'admintools_waftemplate_id';
		$this->table = '#__admintools_waftemplates';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_waftemplates'));

		$fltSubject = $this->input->getString('subject', null);

		if ($fltSubject)
		{
			$fltSubject = '%' . $fltSubject . '%';
			$query->where($db->qn('subject') . ' LIKE ' . $db->q($fltSubject));
		}

		$fltReason = $this->input->getString('reason', null);

		if ($fltReason)
		{
			$fltReason = '%' . $fltReason . '%';
			$query->where($db->qn('reason') . ' LIKE ' . $db->q($fltReason));
		}

		if (!$overrideLimits)
		{
			$ordering  = $this->input->getCmd('ordering', '');
			$direction = $this->input->getCmd('order_dir', '');

			if (!in_array($ordering, array('id', 'subject', 'reason')))
			{
				$ordering = 'admintools_waftemplate_id';
			}

			if (!in_array($direction, array('asc', 'desc')))
			{
				$direction = 'desc';
			}

			$query->order($db->qn($ordering).' '.$direction);
		}

		return $query;
	}

	public function save(array $data = array())
	{
		$db = $this->getDbo();

		if (!$data)
		{
			$data = array(
				'admintools_waftemplate_id' => $this->input->getInt('admintools_waftemplate_id', 0),
				'reason' => $this->input->getString('reason', ''),
				'subject' => $this->input->getString('subject', ''),
				'enabled' => $this->input->getInt('enabled', ''),
				'email_num' => $this->input->getInt('email_num', ''),
				'email_numfreq' => $this->input->getInt('email_numfreq', ''),
				'email_freq' => $this->input->getCmd('email_freq', ''),
				'template' => $this->input->get('template', '', 'raw'),
			);
		}

		if (!isset($data['admintools_waftemplate_id']))
		{
			$data['admintools_waftemplate_id'] = '';
		}

		$data = (object) $data;

		if (!$data->admintools_waftemplate_id)
		{
			$db->insertObject('#__admintools_waftemplates', $data, 'admintools_waftemplate_id');
		}
		else
		{
			$db->updateObject('#__admintools_waftemplates', $data, array('admintools_waftemplate_id'));
		}

		return $data->admintools_waftemplate_id;
	}
}
