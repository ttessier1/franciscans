<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Library\Utils\Ip;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureAutoipfiltering extends AtsystemFeatureAbstract
{
	protected $loadOrder = 10;

	/**
	 * Blocks visitors coming from an automatically banned IP.
	 */
	public function onSystem()
	{
		// Get the visitor's IP address
		$ip = Ip::getIp();

		// Let's get a list of blocked IP ranges
		$db = $this->db;
		$sql = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__admintools_ipautoban'))
			->where($db->qn('ip') . ' = ' . $db->q($ip));
		$db->setQuery($sql);

		try
		{
			$record = $db->loadObject();
		}
		catch (Exception $e)
		{
			$record = null;
		}

		if (empty($record))
		{
			return;
		}

		// Is this record expired?
		$nowDate   = new DateTime('now', new DateTimeZone('GMT'));
		$untilDate = new DateTime($record->until, new DateTimeZone('GMT'));

		$now    = $nowDate->getTimestamp();
		$until  = $untilDate->getTimestamp();

		if ($now > $until)
		{
			// Ban expired. Move the entry and allow the request to proceed.
			$history = clone $record;
			$history->id = null;

			try
			{
				$db->insertObject('#__admintools_ipautobanhistory', $history, 'id');
			}
			catch (Exception $e)
			{
				// Oops...
			}

			$sql = $db->getQuery(true)
				->delete($db->qn('#__admintools_ipautoban'))
				->where($db->qn('ip') . ' = ' . $db->q($ip));
			$db->setQuery($sql);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				// Oops...
			}

			return;
		}

		// Move old entries - The fastest way is to create a INSERT with a SELECT statement
		$sql = 'INSERT INTO ' . $db->qn('#__admintools_ipautobanhistory') . ' (' . $db->qn('id') . ', ' . $db->qn('ip') . ', ' . $db->qn('reason') . ', ' . $db->qn('until') . ')' .
			' SELECT NULL, ' . $db->qn('ip') . ', ' . $db->qn('reason') . ', ' . $db->qn('until') .
			' FROM ' . $db->qn('#__admintools_ipautoban') .
			' WHERE ' . $db->qn('until') . ' < ' . $db->q($nowDate->format('Y-m-d H:i:s'));

		try
		{
			$db->setQuery($sql)->execute();
		}
		catch (Exception $e)
		{
			// Oops...
		}

		$sql = $db->getQuery(true)
			->delete($db->qn('#__admintools_ipautoban'))
			->where($db->qn('until') . ' < ' . $db->q($nowDate->format('Y-m-d H:i:s')));
		$db->setQuery($sql);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			// Oops...
		}

		@ob_end_clean();
		header("HTTP/1.0 403 Forbidden");

		$spammerMessage = $this->cparams->getValue('spammermessage', '');
		$spammerMessage = str_replace('[IP]', $ip, $spammerMessage);

		echo $spammerMessage;
		die();
	}
}
