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
use Akeeba\AdminTools\Library\Date\Date;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\AdminTools\Library\Registry\Registry;
use Akeeba\AdminTools\Library\Transfer\Transfer;
use Akeeba\AdminTools\Library\Uri\Uri;

class Update extends Model
{
	/** @var   string  The URL containing the INI update stream URL */
	protected $updateStreamURL = '';

	/** @var   Registry  A registry object holding the update information */
	protected $updateInfo = null;

	/** @var   string  The table where key-valueinformation is stored */
	protected $tableName = '';

	/** @var   string  The table field which stored the key of the key-value pairs */
	protected $keyField = 'at_key';

	/** @var   string  The table field which stored the value of the key-value pairs */
	protected $valueField = 'at_value';

	/** @var   string  The key tag for the live update serialised information */
	protected $updateInfoTag = 'liveupdate';

	/** @var   string  The key tag for the last check timestamp */
	protected $lastCheckTag = 'liveupdate_lastcheck';

	/** @var   integer  The last update check UNIX timestamp */
	protected $lastCheck = null;

	/** @var   string   Currently installed version */
	protected $currentVersion = '';

	/** @var   string   Currently installed version's date stamp */
	protected $currentDateStamp = '';

	/** @var   string   Minimum stability for reporting updates */
	protected $minStability = 'alpha';

	protected $downloadId = '';

	/**
	 * How to determine if a new version is available. 'different' = if the version number is different,
	 * the remote version is newer, 'vcompare' = use version compare between the two versions, 'newest' =
	 * compare the release dates to find the newest. I suggest using 'different' on most cases.
	 *
	 * @var   string
	 */
	protected $versionStrategy = 'smart';

	public function __construct($input)
	{
		parent::__construct($input);

		$this->currentVersion   = defined('ADMINTOOLSWP_VERSION') ? ADMINTOOLSWP_VERSION : 'dev';
		$this->currentDateStamp = defined('ADMINTOOLSWP_DATE') ? ADMINTOOLSWP_DATE : gmdate('Y-m-d');

		$params = Params::getInstance();
		$this->minStability     = $params->getValue('minstability', 'stable');
		$this->downloadId       = $params->getValue('downloadid', '');

		/**
		 * If the current version is an Alpha, Beta or RC override the minimum stability to the same stability level as
		 * the version currently installed. This lets people testing unstable versions to update to the next unstable
		 * version instead of waiting for us to release a stable. This is especially useful during beta testing phases
		 * of new releases.
		 */
		$currentStability = $this->getStability($this->currentVersion);

		if ($currentStability != 'stable')
		{
			$this->minStability = $currentStability;
		}

		$pro = ADMINTOOLSWP_PRO ? 'pro' : 'core';
		$this->updateStreamURL = 'http://cdn.akeeba.com/updates/admintoolswp' . $pro . '.ini';

		// Testing updates in development versions: define ADMINTOOLSWP_UPDATE_BASEURL in version.php
		if (defined('ADMINTOOLSWP_UPDATE_BASEURL'))
		{
			$pro = ADMINTOOLSWP_PRO ? 'pro' : 'core';

			$this->updateStreamURL = ADMINTOOLSWP_UPDATE_BASEURL . $pro . '.ini';
		}

		$this->tableName       = '#__admintools_storage';
		$this->versionStrategy = 'smart';

		$this->load(false);
	}

	/**
	 * Load the update information into the $this->updateInfo object. The update information will be returned from the
	 * cache. If the cache is expired, the $force flag is set or the ADMINTOOLSWP_PATH  . 'update.ini' file is present the
	 * update information will be reloaded from the source. The update source normally is $this->updateStreamURL. If
	 * the APATH_BASE  . 'update.ini' file is present it's used as the update source instead.
	 *
	 * In short, the ADMINTOOLSWP_PATH  . 'update.ini' file allows you to override update sources for testing purposes.
	 *
	 * @param   bool $force True to force reload the information from the source.
	 *
	 * @return  void
	 */
	public function load($force = false)
	{
		// Clear the update information and last update check timestamp
		$this->lastCheck  = null;
		$this->updateInfo = null;

		// Get a reference to the database
		$db = $this->db;

		// Get the last update timestamp
		$query = $db->getQuery(true)
					->select($db->qn($this->valueField))
					->from($db->qn($this->tableName))
					->where($db->qn($this->keyField) . '=' . $db->q($this->lastCheckTag));

		$this->lastCheck = $db->setQuery($query)->loadResult();

		if (is_null($this->lastCheck))
		{
			$this->lastCheck = 0;
		}

		/**
		 * Override for automated testing
		 *
		 * If the file update.ini exists (next to version.php) force reloading the update information.
		 */
		$fileTestingUpdates = ADMINTOOLSWP_PATH . '/update.ini';

		if (file_exists($fileTestingUpdates))
		{
			$force = true;
		}

		// Do I have to forcible reload from a URL?
		if (!$force)
		{
			// Force reload if more than 6 hours have elapsed
			if (abs(time() - $this->lastCheck) >= 21600)
			{
				$force = true;
			}
		}

		// Try to load from cache
		if (!$force)
		{
			$query = $db->getQuery(true)
						->select($db->qn($this->valueField))
						->from($db->qn($this->tableName))
						->where($db->qn($this->keyField) . '=' . $db->q($this->updateInfoTag));

			$rawInfo = $db->setQuery($query)->loadResult();

			if (empty($rawInfo))
			{
				$force = true;
			}
			else
			{
				$this->updateInfo = new Registry();
				$this->updateInfo->loadString($rawInfo, 'JSON');
			}
		}

		// If it's stuck and we are not forcibly retrying to reload, bail out
		if (!$force && !empty($this->updateInfo) && $this->updateInfo->get('stuck', false))
		{
			return;
		}

		// Maybe we are forced to load from a URL?
		// NOTE: DO NOT MERGE WITH PREVIOUS IF AS THE $force VARIABLE MAY BE MODIFIED THERE!
		if ($force)
		{
			$this->updateInfo = new Registry();
			$this->updateInfo->set('stuck', 1);
			$this->lastCheck = time();

			// Store last update check timestamp
			$o = (object) array(
				$this->keyField   => $this->lastCheckTag,
				$this->valueField => $this->lastCheck,
			);

			$result = false;

			try
			{
				$result = $db->insertObject($this->tableName, $o, $this->keyField);
			}
			catch (\Exception $e)
			{
				$result = false;
			}

			if (!$result)
			{
				try
				{
					$result = $db->updateObject($this->tableName, $o, $this->keyField);
				}
				catch (\Exception $e)
				{
					$result = false;
				}
			}

			// Store update information
			$o = (object) array(
				$this->keyField   => $this->updateInfoTag,
				$this->valueField => $this->updateInfo->toString('JSON'),
			);

			$result = false;

			try
			{
				$result = $db->insertObject($this->tableName, $o, $this->keyField);
			}
			catch (\Exception $e)
			{
				$result = false;
			}

			if (!$result)
			{
				try
				{
					$result = $db->updateObject($this->tableName, $o, $this->keyField);
				}
				catch (\Exception $e)
				{
					$result = false;
				}
			}

			// Simulate a PHP crash for automated testing
			if (defined('AKEEBA_TESTS_SIMULATE_STUCK_UPDATE'))
			{
				die(sprintf('<p id="automated-testing-simulated-crash">This is a simulated crash for automated testing.</p></p>If you are seeing this outside of an automated testing scenario, please delete the line <code>define(\'AKEEBA_TESTS_SIMULATE_STUCK_UPDATE\', 1);</code> from the %s\version.php file</p>', ADMINTOOLSWP_PATH));
			}

			// Try to fetch the update information
			try
			{
				/**
				 * Override for automated testing
				 *
				 * If the file update.ini exists (next to version.php) we use its contents as the update source, without
				 * accessing the update information URL at all. The file is immediately removed.
				 */
				if (is_file($fileTestingUpdates))
				{
					$rawInfo = @file_get_contents($fileTestingUpdates);

					unlink($fileTestingUpdates);
				}
				else
				{
					$download = new Transfer();
					$rawInfo  = $download->getFromURL($this->updateStreamURL);
				}

				$this->updateInfo->loadString($rawInfo, 'INI');
				$this->updateInfo->set('loadedUpdate', ($rawInfo !== false) ? 1 : 0);
				$this->updateInfo->set('stuck', 0);
			}
			catch (\Exception $e)
			{
				// We are stuck. Darn.

				return;
			}

			// If not stuck, loadedUpdate is 1, version key exists and stability key does not exist / is empty, determine the version stability
			$version   = $this->updateInfo->get('version', '');
			$stability = $this->updateInfo->get('stability', '');
			if (
				!$this->updateInfo->get('stuck', 0)
				&& $this->updateInfo->get('loadedUpdate', 0)
				&& !empty($version)
				&& empty($stability)
			)
			{
				$this->updateInfo->set('stability', $this->getStability($version));
			}

			// Since we had to load from a URL, commit the update information to db
			$o = (object) array(
				$this->keyField   => $this->updateInfoTag,
				$this->valueField => $this->updateInfo->toString('JSON'),
			);

			$result = false;

			try
			{
				$result = $db->insertObject($this->tableName, $o, $this->keyField);
			}
			catch (\Exception $e)
			{
				$result = false;
			}

			if (!$result)
			{
				try
				{
					$result = $db->updateObject($this->tableName, $o, $this->keyField);
				}
				catch (\Exception $e)
				{
					$result = false;
				}
			}
		}

		// Check if an update is available and push it to the update information registry
		$this->updateInfo->set('hasUpdate', $this->hasUpdate());

		// Post-process the download URL, appending the Download ID (if defined)
		$link = $this->updateInfo->get('link', '');

		if (!empty($link) && !empty($this->downloadId))
		{
			$link = new Uri($link);
			$link->setVar('dlid', $this->downloadId);
			$this->updateInfo->set('link', $link->toString());
		}
	}

	/**
	 * Is there an update available?
	 *
	 * @return  bool
	 */
	public function hasUpdate()
	{
		$this->updateInfo->set('minstabilityMatch', 1);
		$this->updateInfo->set('platformMatch', 1);

		// Validate the minimum stability
		$stability = strtolower($this->updateInfo->get('stability'));

		switch ($this->minStability)
		{
			case 'alpha':
			default:
				// Reports any stability level as an available update
				break;

			case 'beta':
				// Do not report alphas as available updates
				if (in_array($stability, array('alpha')))
				{
					$this->updateInfo->set('minstabilityMatch', 0);

					return false;
				}
				break;

			case 'rc':
				// Do not report alphas and betas as available updates
				if (in_array($stability, array('alpha', 'beta')))
				{
					$this->updateInfo->set('minstabilityMatch', 0);

					return false;
				}
				break;

			case 'stable':
				// Do not report alphas, betas and rcs as available updates
				if (in_array($stability, array('alpha', 'beta', 'rc')))
				{
					$this->updateInfo->set('minstabilityMatch', 0);

					return false;
				}
				break;
		}

		// Validate the platform compatibility
		$platforms = explode(',', $this->updateInfo->get('platforms', ''));

		if (!empty($platforms))
		{
			$phpVersionParts   = explode('.', PHP_VERSION, 3);
			$currentPHPVersion = $phpVersionParts[0] . '.' . $phpVersionParts[1];

			$platformFound = false;

			$requirePlatformName = Session::get('platformNameForUpdates', 'php');
			$currentPlatform     = Session::get('platformVersionForUpdates', $currentPHPVersion);

			// Check for the platform
			foreach ($platforms as $platform)
			{
				$platform      = trim($platform);
				$platform      = strtolower($platform);
				$platformParts = explode('/', $platform, 2);

				if ($platformParts[0] != $requirePlatformName)
				{
					continue;
				}

				if ((substr($platformParts[1], -1) == '+') && version_compare($currentPlatform, substr($platformParts[1], 0, -1), 'ge'))
				{
					$this->updateInfo->set('platformMatch', 1);
					$platformFound = true;
				}
				elseif ($platformParts[1] == $currentPlatform)
				{
					$this->updateInfo->set('platformMatch', 1);
					$platformFound = true;
				}
			}

			// If we are running inside a CMS perform a second check for the PHP version
			if ($platformFound && ($requirePlatformName != 'php'))
			{
				$this->updateInfo->set('platformMatch', 0);
				$platformFound = false;

				foreach ($platforms as $platform)
				{
					$platform      = trim($platform);
					$platform      = strtolower($platform);
					$platformParts = explode('/', $platform, 2);

					if ($platformParts[0] != 'php')
					{
						continue;
					}

					if ($platformParts[1] == $currentPHPVersion)
					{
						$this->updateInfo->set('platformMatch', 1);
						$platformFound = true;
					}
				}
			}

			if (!$platformFound)
			{
				return false;
			}
		}

		// If the user had the Core version but has entered a Download ID we will always display an update as being
		// available
		if (!ADMINTOOLSWP_PRO && !empty($this->downloadId))
		{
			return true;
		}

		// Apply the version strategy
		$version = $this->updateInfo->get('version', null);
		$date    = $this->updateInfo->get('date', null);

		if (empty($version) || empty($date))
		{
			return false;
		}

		switch ($this->versionStrategy)
		{
			case 'newest':
				return $this->hasUpdateByNewest($version, $date);

				break;

			case 'vcompare':
				return $this->hasUpdateByVersion($version, $date);

				break;

			case 'different':
				return $this->hasUpdateByDifferentVersion($version, $date);

				break;

			case 'smart':
				return $this->hasUpdateByDateAndVersion($version, $date);
				break;
		}

		return false;
	}

	/**
	 * Returns the update information
	 *
	 * @param   bool $force Should we force the fetch of new information?
	 *
	 * @return Registry
	 */
	public function getUpdateInformation($force = false)
	{
		if (is_null($this->updateInfo) || $force)
		{
			$this->load($force);
		}

		return $this->updateInfo;
	}


	/**
	 * Finalises the update. Reserved for future use. DO NOT REMOVE.
	 */
	public function finalise()
	{
		// Reserved for future use. DO NOT REMOVE.
	}

	/**
	 * Get the currently used update stream URL
	 *
	 * @return string
	 */
	public function getUpdateStreamURL()
	{
		return $this->updateStreamURL;
	}

	/**
	 * Normalise the version number to a PHP-format version string.
	 *
	 * @param   string $version The whatever-format version number
	 *
	 * @return  string  A standard formatted version number
	 */
	public function sanitiseVersion($version)
	{
		$test                   = strtolower($version);
		$alphaQualifierPosition = strpos($test, 'alpha-');
		$betaQualifierPosition  = strpos($test, 'beta-');
		$betaQualifierPosition2 = strpos($test, '-beta');
		$rcQualifierPosition    = strpos($test, 'rc-');
		$rcQualifierPosition2   = strpos($test, '-rc');
		$rcQualifierPosition3   = strpos($test, 'rc');
		$devQualifiedPosition   = strpos($test, 'dev');

		if ($alphaQualifierPosition !== false)
		{
			$betaRevision = substr($test, $alphaQualifierPosition + 6);
			if (!$betaRevision)
			{
				$betaRevision = 1;
			}
			$test = substr($test, 0, $alphaQualifierPosition) . '.a' . $betaRevision;
		}
		elseif ($betaQualifierPosition !== false)
		{
			$betaRevision = substr($test, $betaQualifierPosition + 5);
			if (!$betaRevision)
			{
				$betaRevision = 1;
			}
			$test = substr($test, 0, $betaQualifierPosition) . '.b' . $betaRevision;
		}
		elseif ($betaQualifierPosition2 !== false)
		{
			$betaRevision = substr($test, $betaQualifierPosition2 + 5);

			if (!$betaRevision)
			{
				$betaRevision = 1;
			}

			$test = substr($test, 0, $betaQualifierPosition2) . '.b' . $betaRevision;
		}
		elseif ($rcQualifierPosition !== false)
		{
			$betaRevision = substr($test, $rcQualifierPosition + 5);
			if (!$betaRevision)
			{
				$betaRevision = 1;
			}
			$test = substr($test, 0, $rcQualifierPosition) . '.rc' . $betaRevision;
		}
		elseif ($rcQualifierPosition2 !== false)
		{
			$betaRevision = substr($test, $rcQualifierPosition2 + 3);

			if (!$betaRevision)
			{
				$betaRevision = 1;
			}

			$test = substr($test, 0, $rcQualifierPosition2) . '.rc' . $betaRevision;
		}
		elseif ($rcQualifierPosition3 !== false)
		{
			$betaRevision = substr($test, $rcQualifierPosition3 + 5);

			if (!$betaRevision)
			{
				$betaRevision = 1;
			}

			$test = substr($test, 0, $rcQualifierPosition3) . '.rc' . $betaRevision;
		}
		elseif ($devQualifiedPosition !== false)
		{
			$betaRevision = substr($test, $devQualifiedPosition + 6);
			if (!$betaRevision)
			{
				$betaRevision = '';
			}
			$test = substr($test, 0, $devQualifiedPosition) . '.dev' . $betaRevision;
		}

		return $test;
	}

	public function getStability($version)
	{
		$versionParts    = explode('.', $version);
		$lastVersionPart = array_pop($versionParts);

		if (substr($lastVersionPart, 0, 1) == 'a')
		{
			return 'alpha';
		}

		if (substr($lastVersionPart, 0, 1) == 'b')
		{
			return 'beta';
		}

		if (substr($lastVersionPart, 0, 2) == 'rc')
		{
			return 'rc';
		}

		if (substr($lastVersionPart, 0, 3) == 'dev')
		{
			return 'alpha';
		}

		return 'stable';
	}

	/**
	 * Checks if there is an update taking into account only the release date. If the release date is the same then it
	 * takes into account the version.
	 *
	 * @param   string  $version
	 * @param   string  $date
	 *
	 * @return  bool
	 */
	private function hasUpdateByNewest($version, $date)
	{
		if (empty($this->currentDateStamp))
		{
			$mine = new Date('2000-01-01 00:00:00');
		}
		else
		{
			try
			{
				$mine = new Date($this->currentDateStamp);
			}
			catch (\Exception $e)
			{
				$mine = new Date('2000-01-01 00:00:00');
			}
		}

		$theirs = new Date($date);

		/**
		 * Do we have the same time? This happens when we release two versions in the same day. In such cases we have to
		 * check vs the version number.
		 */
		if ($mine->toUnix() == $theirs->toUnix())
		{
			return $this->hasUpdateByVersion($version, $date);
		}

		return ($theirs->toUnix() > $mine->toUnix());
	}

	/**
	 * Checks if there is an update by comparing the version numbers using version_compare()
	 *
	 * @param   string  $version
	 * @param   string  $date
	 *
	 * @return  bool
	 */
	private function hasUpdateByVersion($version, $date)
	{
		$mine = $this->currentVersion;

		if (empty($mine))
		{
			$mine = '0.0.0';
		}

		if (empty($version))
		{
			$version = '0.0.0';
		}

		return version_compare($version, $mine, 'gt');
	}

	/**
	 * Checks if there is an update by looking for a different version number
	 *
	 * @param   string  $version
	 *
	 * @return  bool
	 */
	private function hasUpdateByDifferentVersion($version, $date)
	{
		$mine = $this->currentVersion;

		if (empty($mine))
		{
			$mine = '0.0.0';
		}

		if (empty($version))
		{
			$version = '0.0.0';
		}

		return ($version != $mine);
	}

	private function hasUpdateByDateAndVersion($version, $date)
	{
		$isCurrentDev = in_array(substr($this->currentVersion, 0, 3), array('dev', 'rev'));
		$isUpdateDev = in_array(substr($version, 0, 3), array('dev', 'rev'));

		// Development (rev*) to numbered version; numbered to development; or development to development: use the date
		if ($isCurrentDev || $isUpdateDev)
		{
			return $this->hasUpdateByNewest($version, $date);
		}

		// Identical version number? Use the date
		if ($version == $this->currentVersion)
		{
			return $this->hasUpdateByNewest($version, $date);
		}

		// Otherwise only by version number
		return $this->hasUpdateByVersion($version, $date);
	}
}
