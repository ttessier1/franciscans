<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class UnblockIP extends Model
{
	/**
	 * Removed the current IP from all the "block" lists
	 *
	 * @param	string|array	$ips	IP addresses to check and delete
	 *
	 * @return  bool			Did I had data to delete? If not, we will have to warn the user
	 */
	public function unblockIP($ips)
	{
		$ips = (array) $ips;

		/** @var AutoBannedAddresses $autoban */
		$autoban = new AutoBannedAddresses($this->input);

		/** @var IPAutoBanHistories $history */
		$history = new IPAutoBanHistories($this->input);

		/** @var BlacklistedAddresses $black */
		$black = new BlacklistedAddresses($this->input);

		/** @var SecurityExceptions $log */
		$log = new SecurityExceptions($this->input);

		$db    = Wordpress::getDb();
		$found = false;

		// Let's delete all the IPs. We are going to directly use the database since it would be faster
		// than loading the record and then deleting it
		foreach ($ips as $ip)
		{
			// In WordPress we do not have the concept of "state" inside the models. So we're going to manipulate the
			// input object and then pass it back to the model
			$fake_input = clone $this->input;
			$fake_input->set('ip', $ip);

			$autoban->setInput($fake_input);
			$history->setInput($fake_input);
			$black->setInput($fake_input);
			$log->setInput($fake_input);

			if (count($autoban->getItems(true)))
			{
				$found = true;

				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_ipautoban'))
							->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			if (count($history->getItems(true)))
			{
				$found = true;

				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_ipautobanhistory'))
							->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			if (count($black->getItems(true)))
			{
				$found = true;

				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_ipblock'))
							->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			// I have to delete the log of security exceptions, too. Otherwise at the next check the user will be
			// banned once again
			if (count($log->getItems(true)))
			{
				$found = true;

				$query = $db->getQuery(true)
							->delete($db->qn('#__admintools_log'))
							->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}
		}

		if (!$found)
		{
			return false;
		}

		return true;
	}
}
