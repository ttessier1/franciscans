<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureRemoveoldlog extends AtsystemFeatureAbstract
{
	protected $loadOrder = 110;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->params->getValue('maxlogentries', 0) > 0);
	}

	/**
	 * Deletes old log entries, keeping up to maxlogentries entries.
	 */
	public function onSystem()
	{
		// Delete up to 100 old entries
		$maxEntries = $this->params->getValue('maxlogentries', 0);

		$db = $this->db;
		$query = $db->getQuery(true)
					->select($db->qn('id'))
					->from($db->qn('#__admintools_log'))
					->order($db->qn('id') . ' DESC');

		$db->setQuery($query, $maxEntries, 100);
		$ids = $db->loadColumn(0);

		if (!count($ids))
		{
			return;
		}

		$temp = array();

		foreach ($ids as $id)
		{
			$temp[] = $db->q($id);
		}

		$ids = implode(',', $temp);

		$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_log'))
					->where($db->qn('id') . ' IN(' . $ids . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $exc)
		{
			// Do nothing on DB exception
		}
	}
} 
