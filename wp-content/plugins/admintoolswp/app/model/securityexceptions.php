<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class SecurityExceptions extends Model
{
	public function __construct(Input $input)
	{
		parent::__construct($input);

		$this->pk    = 'id';
		$this->table = '#__admintools_log';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
					->from($db->qn('#__admintools_log') . ' AS ' . $db->qn('l'));

		$query
			->select(array(
				$db->qn('l') . '.*',
				'CASE COALESCE(' . $db->qn('b') . '.' . $db->qn('ip') . ', ' . $db->q(0) . ') WHEN ' . $db->q(0)
				. ' THEN ' . $db->q('0') . ' ELSE ' . $db->q('1') . ' END AS ' . $db->qn('block')
			))
			->join('LEFT OUTER',
				$db->qn('#__admintools_ipblock') . ' AS ' . $db->qn('b') .
				'ON (' . $db->qn('b') . '.' . $db->qn('ip') . ' = ' .
				$db->qn('l') . '.' . $db->qn('ip') . ')'
			);

		$fltIP = $this->input->getString('ip', null);

		if ($fltIP)
		{
			$fltIP = '%' . $fltIP . '%';
			$query->where($db->qn('l') . '.' . $db->qn('ip') . ' LIKE ' . $db->q($fltIP));
		}

		$fltURL = $this->input->getString('url', null);

		if ($fltURL)
		{
			$fltURL = '%' . $fltURL . '%';
			$query->where($db->qn('url') . ' LIKE ' . $db->q($fltURL));
		}

		$fltReason = $this->input->getCmd('reason', null);

		if ($fltReason)
		{
			$query->where($db->qn('reason') . ' = ' . $db->q($fltReason));
		}

		if (!$overrideLimits)
		{
			$ordering  = $this->input->getCmd('ordering', '');
			$direction = $this->input->getCmd('order_dir', '');

			if (!in_array($ordering, array('logdate', 'reason', 'url', 'ip')))
			{
				$ordering = 'logdate';
			}

			if (!in_array($direction, array('asc', 'desc')))
			{
				$direction = 'desc';
			}

			$query->order($db->qn($ordering).' '.$direction);
		}

		return $query;
	}

	/**
	 * Helper function to retrieve the amount of logged exceptions in a given timeframe
	 *
	 * @param   string  $fromDate   Starting datetime in format Y-m-d H:i:s
	 * @param   string  $toDate     Ending datetime in format Y-m-d H:i:s
	 *
	 * @return  int     The amount of exceptions logged
	 */
	public function getExceptionsInRange($fromDate, $toDate)
	{
		$db    = $this->getDbo();
		$query = $this->buildQuery(true);

		$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';

		if (!preg_match($regex, $fromDate))
		{
			$fromDate = '2000-01-01 00:00:00';
		}

		$date = new \DateTime($fromDate, null);
		$date->setTime(0,0,0);
		$query->where($db->qn('logdate') . ' >= ' . $db->q($date->format('Y-m-d H:i:s')));

		$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';

		if (!preg_match($regex, $toDate))
		{
			$toDate = '2037-01-01 00:00:00';
		}

		$date = new \DateTime($toDate, null);
		$date->setTime(23,59,59);
		$query->where($db->qn('logdate') . ' <= ' . $db->q($date->format('Y-m-d H:i:s')));

		$query->clear('select');
		$query->select('COUNT(*)');

		$count = $db->setQuery($query)->loadResult();

		return $count;
	}

	public function getExceptionsByDate($fromDate, $toDate)
	{
		$db    = $this->getDbo();
		$query = $this->buildQuery(true);

		// Let's clear the default select to improve performance
		$query->clear('select');

		$query->select(array(
			'DATE(' . $db->qn('l') . '.' . $db->qn('logdate') . ') AS ' . $db->qn('date'),
			'COUNT(' . $db->qn('l') . '.' . $db->qn('id') . ') AS ' . $db->qn('exceptions')
		));

		$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';

		if (!preg_match($regex, $fromDate))
		{
			$fromDate = '2000-01-01 00:00:00';
		}

		$date = new \DateTime($fromDate, null);
		$date->setTime(0,0,0);
		$query->where($db->qn('logdate') . ' >= ' . $db->q($date->format('Y-m-d H:i:s')));

		$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';

		if (!preg_match($regex, $toDate))
		{
			$toDate = '2037-01-01 00:00:00';
		}

		$date = new \DateTime($toDate, null);
		$date->setTime(23,59,59);
		$query->where($db->qn('logdate') . ' <= ' . $db->q($date->format('Y-m-d H:i:s')));

		$query->order('DATE(' . $db->qn('l') . '.' . $db->qn('logdate') . ') ASC');
		$query->group(array(
			'DATE(' . $db->qn('l') . '.' . $db->qn('logdate') . ')'
		));

		return $db->setQuery($query)->loadObjectList();
	}

	public function getExceptionsByType($fromDate, $toDate)
	{
		$db    = $this->getDbo();
		$query = $this->buildQuery(true);

		// Let's clear the default select to improve performance
		$query->clear('select');

		$query->select(array(
			$db->qn('l') . '.' . $db->qn('reason'),
			'COUNT(' . $db->qn('l') . '.' . $db->qn('id') . ') AS ' . $db->qn('exceptions')
		));

		$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';

		if (!preg_match($regex, $fromDate))
		{
			$fromDate = '2000-01-01 00:00:00';
		}

		$date = new \DateTime($fromDate, null);
		$date->setTime(0,0,0);
		$query->where($db->qn('logdate') . ' >= ' . $db->q($date->format('Y-m-d H:i:s')));

		$regex = '/^\d{1,4}(\/|-)\d{1,2}(\/|-)\d{2,4}[[:space:]]{0,}(\d{1,2}:\d{1,2}(:\d{1,2}){0,1}){0,1}$/';

		if (!preg_match($regex, $toDate))
		{
			$toDate = '2037-01-01 00:00:00';
		}

		$date = new \DateTime($toDate, null);
		$date->setTime(23,59,59);
		$query->where($db->qn('logdate') . ' <= ' . $db->q($date->format('Y-m-d H:i:s')));

		$query->order($db->qn('l') . '.' . $db->qn('reason') . ' ASC');
		$query->group(array(
			$db->qn('l') . '.' . $db->qn('reason')
		));

		return $db->setQuery($query)->loadObjectList();
	}
}
