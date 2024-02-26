<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\HtaccessMaker;
use Akeeba\AdminTools\Admin\Model\OptimizeWaf;
use Akeeba\AdminTools\Library\Database\Installer;
use Akeeba\AdminTools\Library\Filesystem\File;
use Akeeba\AdminTools\Library\Input\Input;

/**
 * @package        admintoolswp
 * @copyright      Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU GPL version 3 or later
 */
abstract class AdminToolsInstaller
{
	/**
	 * Obsolete files and folders to remove from the free version only. This is used when you move a feature from the
	 * free version of your extension to its paid version. If you don't have such a distinction you can ignore this.
	 *
	 * @var   array
	 */
	private static $removeFilesFree = [
		'files'   => [
			// Remove Pro feature files
			'app/plugins/waf/feature/badwords.php',
			'app/plugins/waf/feature/blockedemaildomains.php',
			'app/plugins/waf/feature/customadminfolder.php',
			'app/plugins/waf/feature/customerrorreporting.php',
			'app/plugins/waf/feature/disablexmlrpc.php',
			'app/plugins/waf/feature/emailonlogin.php',
			'app/plugins/waf/feature/geoblock.php',
			'app/plugins/waf/feature/ipblacklist.php',
			'app/plugins/waf/feature/ipwhitelist.php',
			'app/plugins/waf/feature/leakedpwd.php',
			'app/plugins/waf/feature/loginerrormsg.php',
			'app/plugins/waf/feature/nonewadmins.php',
			'app/plugins/waf/feature/removeblogclient.php',
			'app/plugins/waf/feature/removerss.php',
			'app/plugins/waf/feature/sessionduration.php',
			'app/plugins/waf/feature/trackfailedlogins.php',
		],
		'folders' => [],
	];

	/**
	 * Obsolete files and folders to remove from both paid and free releases. This is used when you refactor code and
	 * some files inevitably become obsolete and need to be removed.
	 *
	 * @var   array
	 */
	private static $removeFilesAllVersions = [
		'files'   => [
			// Removed Secret Word feature
			'app/plugins/waf/feature/secretword.php',
			// Removed Geographic IP Blocking
			'app/plugins/waf/feature/geoblock.php',
			'app/controller/geographicblocking.php',
			'app/model/geographicblocking.php',
			// Old uncompressed CSS
			'app/media/css/backend.css',

			// Remove DFI feature
			'app/plugins/waf/feature/dfishield.php',

			// Changelog PNG images
			'app/media/images/changelog.png',
		],
		'folders' => [
			// Old File Change Scanner files
			'app/engine',
			'app/platform',
			// Removed Geographic IP Blocking
			'app/assets/akgeoip',
			'app/library/geoip',
			'app/view/geographicblocking.php',
		],
	];

	private static $isPaid = false;

	public static function uninstall()
	{
		global $wpdb;

		// Clear the cron
		wp_clear_scheduled_hook('admintoolswp_cron');

		// Clear our hook to update functions. It seems WordPress invokes them after deleting the files, resulting in a
		// hidden fatal error during the uninstall process
		remove_filter('pre_set_site_transient_update_plugins', ['AdminToolsWPUpdater', 'getUpdateInformation'], 10);

		//Remove the mu-plugins
		$mu_folder = ABSPATH . 'wp-content/mu-plugins';

		if (defined('WPMU_PLUGIN_DIR') && WPMU_PLUGIN_DIR)
		{
			$mu_folder = WPMU_PLUGIN_DIR;
		}

		if (file_exists($mu_folder . '/admintoolswp.php'))
		{
			unlink($mu_folder . '/admintoolswp.php');
		}

		// Disable auto-prepend mode
		$input    = new Input();
		$optimize = new OptimizeWaf($input);

		try
		{
			$optimize->disableAutoPrepend();
		}
		catch (Exception $e)
		{
			// Something bad happened. Let's cross our fingers and hope it won't break the site
		}

		// If we're under Apache, let's remove any custom rule we previously set
		if (
			class_exists(ServerTechnology::class) &&
			class_exists(HtaccessMaker::class) &&
			ServerTechnology::isHtaccessSupported()
		)
		{
			$htaccess = new HtaccessMaker($input);
			$htaccess->nuke();
		}

		$db          = Wordpress::getDb();
		$dbInstaller = new Installer($db, ADMINTOOLSWP_PATH . '/app/sql/xml');

		// Uninstall tables
		$dbInstaller->removeSchema();

		$db->setQuery("DELETE FROM `#__options` WHERE `option_name` = 'admintoolswp_plugin_dir'")->execute();
		$db->setQuery("DELETE FROM `#__usermeta` WHERE `meta_key` = 'admintoolswp_messages'")->execute();
	}

	/**
	 * This function is called everytime we install, update or activate our plugin
	 */
	public static function installOrUpdate()
	{
		// Require WordPress 6.0 or later
		if (version_compare(get_bloginfo('version'), '6.0', 'lt'))
		{
			deactivate_plugins('admintoolswp.php');

			return;
		}

		// Check that we have the correct PHP version
		$minPHP = defined('ADMINTOOLSWP_MIN_PHP') ? ADMINTOOLSWP_MIN_PHP : '7.4.0';

		if (version_compare(PHP_VERSION, $minPHP, 'lt'))
		{
			deactivate_plugins('admintoolswp.php');

			return;
		}

		unset($minPHP);


		// Clear opcache before starting the update process
		self::clearOpcodeCaches();

		// Register cron hook and our custom interval
		if (!wp_next_scheduled('admintoolswp_cron'))
		{
			wp_schedule_event(time(), 'five_min', 'admintoolswp_cron');
		}

		if (!wp_next_scheduled('admintoolswp_cron_hourly'))
		{
			wp_schedule_event(time(), 'hourly', 'admintoolswp_cron_hourly');
		}

		// First of all save plugin path on filesystem, for installation on different folder names
		self::savePluginPath();

		$admintools_dir = get_option('admintoolswp_plugin_dir');
		$htaccess_path  = WP_PLUGIN_DIR . '/' . $admintools_dir . '/app/view/htaccessmaker';
		self::$isPaid   = defined('ADMINTOOLSWP_PRO') ? ADMINTOOLSWP_PRO : is_dir($htaccess_path);

		// Include the bootstrap file so it will register our autoloader
		include_once WP_PLUGIN_DIR . '/' . $admintools_dir . '/helpers/bootstrap.php';

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

		// Install (copy) the mu plugin inside the mu-folder
		self::installPlugin();

		// Which files should I remove?
		if (self::$isPaid)
		{
			// This is the paid version, only remove the removeFilesAllVersions files
			$removeFiles = self::$removeFilesAllVersions;
		}
		else
		{
			// This is the free version, remove the removeFilesAllVersions and removeFilesFree files
			$removeFiles['files']   = array_merge(self::$removeFilesAllVersions['files'], self::$removeFilesFree['files']);
			$removeFiles['folders'] = array_merge(self::$removeFilesAllVersions['folders'], self::$removeFilesFree['folders']);
		}

		// Remove obsolete files and folders
		self::removeFilesAndFolders($removeFiles);

		// Clear opcache AFTER we did our job
		self::clearOpcodeCaches();
	}

	/**
	 * This function will inspect if the given file (usually one not included in the plugin folder) is up to date.
	 * It will extract the version from the passed constant and check vs the current one.
	 *
	 * @param   string  $file      File to check
	 * @param   string  $constant  Constant that should be extracted
	 *
	 * @return bool
	 */
	public static function fileUpToDate($file, $constant)
	{
		// No file? This means that the user disabled it or not enabled the feature. Let's stop here
		if (!file_exists($file))
		{
			return true;
		}

		$contents = file_get_contents($file);
		$lines    = explode("\n", $contents);

		foreach ($lines as $line)
		{
			if (strpos($line, $constant) === false)
			{
				continue;
			}

			$version = explode(',', $line);
			$version = trim($version[1], ' );');
			$version = str_replace('\'', '', $version);
			$version = str_replace('"', '', $version);

			return version_compare(ADMINTOOLSWP_VERSION, $version, 'eq');
		}

		return true;
	}

	public static function updateAutoPrependFile()
	{
		$waf_file = ABSPATH . 'admintools-waf.php';

		// If the file exists, let's update it, no matter what
		if (file_exists($waf_file))
		{
			$input       = new Input();
			$optimizeWaf = new OptimizeWaf($input);

			$optimizeWaf->createAutoPrependFile($waf_file);
		}
	}

	/**
	 * Clear PHP opcode caches
	 *
	 * @return  void
	 */
	public static function clearOpcodeCaches()
	{
		// Always reset the OPcache if it's enabled. Otherwise there's a good chance the server will not know we are
		// replacing .php scripts. This is a major concern since PHP 5.5 included and enabled OPcache by default.
		if (function_exists('opcache_reset'))
		{
			opcache_reset();
		}
		// Also do that for APC cache
		elseif (function_exists('apc_clear_cache'))
		{
			@apc_clear_cache();
		}
	}

	public static function savePluginPath()
	{
		$baseUrlParts = explode('/', plugins_url('', __FILE__));
		array_pop($baseUrlParts);
		$pluginDir = end($baseUrlParts);

		// Always update the plugin path, no matter what
		update_option('admintoolswp_plugin_dir', $pluginDir);
	}

	public static function installPlugin()
	{
		// Copy the mu-plugins in the correct folder
		$mu_folder = ABSPATH . 'wp-content/mu-plugins';

		if (defined('WPMU_PLUGIN_DIR') && WPMU_PLUGIN_DIR)
		{
			$mu_folder = WPMU_PLUGIN_DIR;
		}

		if (!is_dir($mu_folder))
		{
			mkdir($mu_folder, 0755, true);
		}

		// If the file already exists and it's a link, do not copy over (local development)
		if (is_link($mu_folder . '/admintoolswp.php'))
		{
			return;
		}

		$result = copy(ADMINTOOLSWP_PATH . '/app/plugins/mu-plugins/admintoolswp.php', $mu_folder . '/admintoolswp.php');

		// If something went bad, stop here since it won't work as intended
		if (!$result)
		{
			throw new \RuntimeException(sprintf('Can not copy mu-plugins into the folder %s', $mu_folder));
		}
	}

	/**
	 * Removes obsolete files and folders
	 *
	 * @param   array  $removeList  The files and directories to remove
	 */
	private static function removeFilesAndFolders($removeList)
	{
		$plugin_dir  = get_option('admintoolswp_plugin_dir');
		$plugin_path = rtrim(WP_PLUGIN_DIR . '/' . $plugin_dir, '/') . '/';

		// Remove files
		if (isset($removeList['files']) && !empty($removeList['files']))
		{
			foreach ($removeList['files'] as $file)
			{
				$f = $plugin_path . $file;

				if (!is_file($f))
				{
					continue;
				}

				// TODO Use WordPress Filesystem API
				@unlink($f);
			}
		}

		// Remove folders
		if (isset($removeList['folders']) && !empty($removeList['folders']))
		{
			// TODO At the moment we use our own adapter, in the future we'll have to use WordPress FS API
			$filesystem = new File();

			foreach ($removeList['folders'] as $folder)
			{
				$f = $plugin_path . $folder;

				if (!is_dir($f))
				{
					continue;
				}

				$filesystem->rmdir($f, true);
			}
		}
	}
}
