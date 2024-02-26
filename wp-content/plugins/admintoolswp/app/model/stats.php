<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Encrypt\Randval;
use Akeeba\AdminTools\Library\Uri\Uri;
use AkeebaUsagestats;
use Exception;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class Stats extends Model
{
	/**
	 * Get an existing unique site ID or create a new one
	 *
	 * @return string
	 */
	public function getSiteId()
	{
		// Can I load a site ID from the database?
		$siteId  = $this->getCommonVariable('stats_siteid', null);
		// Can I load the site Url from the database?
		$siteUrl = $this->getCommonVariable('stats_siteurl', null);

		// No id or the saved URL is not the same as the current one (ie site restored to a new url)?
		// Create a new, random site ID and save it to the database
		if (empty($siteId) || (md5(Uri::base()) != $siteUrl))
		{
			$siteUrl = md5(Uri::base());
			$this->setCommonVariable('stats_siteurl', $siteUrl);

			$random = new Randval();

			$randomData = $random->generate(120);
			$siteId = sha1($randomData);

			$this->setCommonVariable('stats_siteid', $siteId);
		}

		return $siteId;
	}

	/**
	 * Send site information to the remove collection service
	 *
	 * @param  bool  $useIframe  Should I use an IFRAME?
	 *
	 * @return bool
	 */
	public function collectStatistics($useIframe)
	{
		// Do not collect statistics on localhost
		if (
			(strpos(Uri::root(), 'localhost') !== false) ||
			(strpos(Uri::root(), '127.0.0.1') !== false)
		)
		{
			return false;
		}

		// Make sure there is a site ID set
		$siteId = $this->getSiteId();

		// UsageStats file is missing, no need to continue
		if (!file_exists(ADMINTOOLSWP_PATH . '/app/assets/stats/usagestats.php'))
		{
			return false;
		}

		if (!class_exists('AkeebaUsagestats', false))
		{
			require_once ADMINTOOLSWP_PATH . '/app/assets/stats/usagestats.php';
		}

		// UsageStats file is missing, no need to continue
		if (!class_exists('AkeebaUsagestats'))
		{
			return false;
		}

		$lastrun = $this->getCommonVariable('stats_lastrun', 0);

		// Data collection is turned off
		$params = Params::getInstance();

		if (!$params->getValue('stats_enabled', 1))
		{
			return false;
		}

		// It's not time to collect the stats
		if (time() < ($lastrun + 3600 * 24))
		{
			return false;
		}

		require_once ADMINTOOLSWP_PATH . '/version.php';

		$db = Wordpress::getDb();
		$stats = new AkeebaUsagestats();

		$stats->setSiteId($siteId);

		// I can't use list since dev release don't have any dots
		$at_parts = explode('.', ADMINTOOLSWP_VERSION);
		$at_major = $at_parts[0];
		$at_minor = isset($at_parts[1]) ? $at_parts[1] : '';
		$at_revision = isset($at_parts[2]) ? $at_parts[2] : '';

		list($php_major, $php_minor, $php_revision) = explode('.', phpversion());
		$php_qualifier = strpos($php_revision, '~') !== false ? substr($php_revision, strpos($php_revision, '~')) : '';

		$cmsType  = function_exists('classicpress_version') ? 3 : 2;

		if (function_exists('get_bloginfo'))
		{
			$WordPressVersion = get_bloginfo('version');
		}
		else
		{
			global $wp_version;

			$WordPressVersion = $wp_version;
		}

		/**
		 * Anticipate releases without a revision number, e.g. 5.0. In this case we only have two dots. We convert them
		 * to the format 5.0.0 before using explode to prevent $cms_revision from being null.
		 */
		$test = $WordPressVersion;
		str_replace('.', '/', $test, $dots);
		list($cms_major, $cms_minor, $cms_revision) = explode('.', ($dots == 1 ? ($WordPressVersion . '.0') : $WordPressVersion));

		list($db_major, $db_minor, $db_revision) = explode('.', $db->getVersion());
		$db_qualifier = strpos($db_revision, '~') !== false ? substr($db_revision, strpos($db_revision, '~')) : '';


		$stats->setValue('dt', 1);							// WordPress runs only on MySQL
		$stats->setValue('sw', ADMINTOOLSWP_PRO ? 15 : 16);	// software
		$stats->setValue('pro', ADMINTOOLSWP_PRO); 			// pro
		$stats->setValue('sm', $at_major); 					// software_major
		$stats->setValue('sn', $at_minor); 					// software_minor
		$stats->setValue('sr', $at_revision);				// software_revision
		$stats->setValue('pm', $php_major);					// php_major
		$stats->setValue('pn', $php_minor);					// php_minor
		$stats->setValue('pr', $php_revision);				// php_revision
		$stats->setValue('pq', $php_qualifier);				// php_qualifiers
		$stats->setValue('dm', $db_major);					// db_major
		$stats->setValue('dn', $db_minor);					// db_minor
		$stats->setValue('dr', $db_revision);				// db_revision
		$stats->setValue('dq', $db_qualifier);				// db_qualifiers
		$stats->setValue('ct', $cmsType);				    // cms_type
		$stats->setValue('cm', $cms_major);					// cms_major
		$stats->setValue('cn', $cms_minor);					// cms_minor
		$stats->setValue('cr', $cms_revision);				// cms_revision

		// Store the last execution time. We must store it even if we fail since we don't want a failed stats collection
		// to cause the site to stop responding.
		$this->setCommonVariable('stats_lastrun', time());

		$return = $stats->sendInfo($useIframe);

		return $return;
	}

	/**
	 * Load a variable from the common variables table. If it doesn't exist it returns $default
	 *
	 * @param  string  $key      The key to load
	 * @param  mixed   $default  The default value if the key doesn't exist
	 *
	 * @return mixed The contents of the key or null if it's not present
	 */
	public function getCommonVariable($key, $default = null)
	{
		$db = Wordpress::getDb();

		$query = $db->getQuery(true)
					->select($db->qn('value'))
					->from($db->qn('#__akeeba_common'))
					->where($db->qn('key') . ' = ' . $db->q($key));

		try
		{
			$db->setQuery($query);
			$result = $db->loadResult();
		}
		catch (Exception $e)
		{
			$result = $default;
		}

		return $result;
	}

	/**
	 * Set a variable to the common variables table.
	 *
	 * @param  string  $key    The key to save
	 * @param  mixed   $value  The value to save
	 */
	public function setCommonVariable($key, $value)
	{
		$db = Wordpress::getDb();
		$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->qn('#__akeeba_common'))
					->where($db->qn('key') . ' = ' . $db->q($key));

		try
		{
			$db->setQuery($query);
			$count = $db->loadResult();
		}
		catch (Exception $e)
		{
			return;
		}

		if (!$count)
		{
			$query = $db->getQuery(true)
						->insert($db->qn('#__akeeba_common'))
						->columns(array($db->qn('key'), $db->qn('value')))
						->values($db->q($key) . ', ' . $db->q($value));
		}
		else
		{
			$query = $db->getQuery(true)
						->update($db->qn('#__akeeba_common'))
						->set($db->qn('value') . ' = ' . $db->q($value))
						->where($db->qn('key') . ' = ' . $db->q($key));
		}

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
		}
	}
}
