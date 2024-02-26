<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Session;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\Scanner\Complexify;
use Akeeba\AdminTools\Library\Database\Installer;
use Akeeba\AdminTools\Library\Encrypt\Randval;
use Akeeba\AdminTools\Library\Utils\Ip;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

class ControlPanel extends Model
{
	/**
	 * Do I need to show the Quick Setup Wizard?
	 *
	 * @return  bool
	 */
	public function needsQuickSetupWizard()
	{
		$params = Storage::getInstance();

		return $params->getValue('quickstart', 0) == 0;
	}

	/**
	 * Delete old log files (with a .log extension) always. If the logging feature is disabled (either the text debug
	 * log or logging in general) also delete the .php log files.
	 */
	public function deleteOldLogs()
	{
		$logpath = ADMINTOOLSWP_PATH.'/app/log';

		$files   = [
			$logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.log',
			$logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.log.1',
		];

		$WAFparams = Storage::getInstance();
		$textLogs  = $WAFparams->getValue('logfile', 0);
		$allLogs   = $WAFparams->getValue('logbreaches', 1);

		if (!$textLogs || !$allLogs)
		{
			$files = array_merge($files, [
				$logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.php',
				$logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.1.php',

			]);
		}

		foreach ($files as $file)
		{
			if (!@file_exists($file))
			{
				continue;
			}

			@unlink($file);
		}
	}

	/**
	 * Get the most likely visitor IP address, reported by the server
	 *
	 * @return  string
	 */
	public function getVisitorIP()
	{
		$internalIP = Ip::getIp();

		if ((strpos($internalIP, '::') === 0) && (strstr($internalIP, '.') !== false))
		{
			$internalIP = substr($internalIP, 2);
		}

		return $internalIP;
	}

	/**
	 * Checks if we have detected private network IPs AND the IP Workaround feature is turned off
	 *
	 * @return bool
	 */
	public function needsIpWorkaroundsForPrivNetwork()
	{
		$WAFparams = Storage::getInstance();
		$params    = Params::getInstance();

		// If IP Workarounds is disabled AND we have detected private IPs, show the warning
		if (!$WAFparams->getValue('ipworkarounds', -1) && ($params->getValue('detected_exceptions_from_private_network') === 1))
		{
			return true;
		}

		return false;
	}

	/**
	 * Sets the IP workarounds or ignores the warning
	 *
	 * @param $state
	 */
	public function setIpWorkarounds($state)
	{
		if ($state)
		{
			$WAFparams = Storage::getInstance();
			$WAFparams->setValue('ipworkarounds', 1, true);
		}
		else
		{
			// If the user wants to ignore the warning, let's set every flag about IP workarounds to -1 (so they will be ignored)
			$params = Params::getInstance();
			$params->setValue('detected_exceptions_from_private_network', -1);
			// TODO Not yet implemented
			// $params->setValue('detected_proxy_header', -1);
			$params->save();
		}
	}

	/**
	 * Does the user need to enter a Download ID in the component's Options page?
	 *
	 * @return  bool
	 */
	public function needsDownloadID()
	{
		// Do I need a Download ID?
		if (!ADMINTOOLSWP_PRO)
		{
			return false;
		}

		$params = Params::getInstance();
		$dlid   = $params->getValue('downloadid', '');

		if (!preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks the database for missing / outdated tables using the $dbChecks
	 * data and runs the appropriate SQL scripts if necessary.
	 *
	 * @return  $this
	 */
	public function checkAndFixDatabase()
	{
		// Install or update database
		$db          = Wordpress::getDb();
		$dbInstaller = new Installer($db, ADMINTOOLSWP_PATH . '/app/sql/xml');

		try
		{
			$dbInstaller->updateSchema();
		}
		catch (\Exception $e)
		{
			// TODO Correctly handle stuck updates
		}

		return $this;
	}

	/**
	 * Checks all the available places if we just blocked our own IP?
	 *
	 * @param	string	$externalIp	 Additional IP address to check
	 *
	 * @return  bool
	 */
	public function isMyIPBlocked($externalIp = null)
	{
		// The Core version has no WAF, so there's no point on running it
		if (!defined('ADMINTOOLSWP_PRO') || !ADMINTOOLSWP_PRO)
		{
			return false;
		}

		// First let's get the current IP of the user
		$ipList[] = $this->getVisitorIP();

		if ($externalIp)
		{
			$ipList[] = $externalIp;
		}

		// Then for each ip let's check if it's in any "blocked" list
		foreach ($ipList as $ip)
		{
			/** @var AutoBannedAddresses $autoban */
			/** @var BlacklistedAddresses $black */
			/** @var IPAutoBanHistories $history */
			$dummyInput = $this->input;
			$dummyInput->set('ip', $ip);

			$autoban = new AutoBannedAddresses($dummyInput);
			$black   = new BlacklistedAddresses($dummyInput);
			$history = new IPAutoBanHistories($dummyInput);

			if (count($autoban->getItems(true)))
			{
				return true;
			}

			if (count($history->getItems(true)))
			{
				return true;
			}

			if (count($black->getItems(true)))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Removed the current IP from all the "block" lists
	 *
	 * @param	string	$externalIp	Additional IP address to check
	 *
	 * @return  void
	 */
	public function unblockMyIP($externalIp = null)
	{
		// First let's get the current IP of the user
		$ipList[] = $this->getVisitorIP();

		if ($externalIp)
		{
			$ipList[] = $externalIp;
		}

		$db  = $this->getDbo();

		// Let's delete all the IP. We are going to directly use the database since it would be faster
		// than loading the record and then deleting it
		foreach ($ipList as $ip)
		{
			/** @var AutoBannedAddresses $autoban */
			/** @var BlacklistedAddresses $black */
			/** @var IPAutoBanHistories $history */
			$dummyInput = $this->input;
			$dummyInput->set('ip', $ip);

			$autoban = new AutoBannedAddresses($dummyInput);
			$black   = new BlacklistedAddresses($dummyInput);
			$log     = new SecurityExceptions($dummyInput);
			$history = new IPAutoBanHistories($dummyInput);

			if (count($autoban->getItems(true)))
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_ipautoban'))
					->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			if (count($history->getItems(true)))
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_ipautobanhistory'))
					->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			if (count($black->getItems(true)))
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_ipblock'))
					->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}

			// I have to delete the log of security exceptions, too. Otherwise at the next check the user will be
			// banned once again
			if (count($log->getItems(true)))
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_log'))
					->where($db->qn('ip') . ' = ' . $db->q($ip));
				$db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Check the strength of the Secret Word for front-end and remote scans. If it is insecure return the reason it
	 * is insecure as a string. If the Secret Word is secure return an empty string.
	 *
	 * @return  string
	 */
	public function getFrontendSecretWordError()
	{
		$params = Params::getInstance();

		// Is frontend backup enabled?
		$febEnabled = $params->getValue('frontend_enable', 0) != 0;

		if (!$febEnabled)
		{
			return '';
		}

		$secretWord = $params->getValue('frontend_secret_word', '');

		try
		{
			Complexify::isStrongEnough($secretWord);
		}
		catch (\RuntimeException $e)
		{
			// Ah, the current Secret Word is bad. Create a new one if necessary.
			$newSecret = $session = Session::get('newSecretWord', null);

			if (empty($newSecret))
			{
				$random    = new Randval();
				$newSecret = $random->generateString(32);
				Session::set('newSecretWord', $newSecret);
			}

			return $e->getMessage();
		}

		return '';
	}
}
