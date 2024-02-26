<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Model\ControlPanel;
use Akeeba\AdminTools\Admin\Model\Update;
use Akeeba\AdminTools\Library\Exception\Update\ConnectionError;
use Akeeba\AdminTools\Library\Exception\Update\PlatformError;
use Akeeba\AdminTools\Library\Exception\Update\StabilityError;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Registry\Registry;

/**
 * This class will take care of bridging WordPress update system and Admin Tools for WordPress package, fetching the
 * info from the plugin and passing back to WordPress.
 */
abstract class AdminToolsWPUpdater
{
	/**
	 * Private static variable keys that belong to our frozen state, stored in a site transient.
	 */
	const STATE_KEYS = [
		'needsDownloadID',
		'connectionError',
		'platformError',
		'downloadLink',
		'cantUseWpUpdate',
		'stabilityError',
	];

	/** @var bool Do I need the Download ID? */
	protected static $needsDownloadID = false;

	/** @var bool Did I have a connection error while */
	protected static $connectionError = false;

	/** @var bool Do I have a platform error? (Wrong PHP or WP version) */
	protected static $platformError = false;

	/** @var string    Stores the download link. In this way we can run our logic only on our download links */
	protected static $downloadLink;

	/** @var bool    Am I in an ancient version of WordPress, were the integrated system is not usable? */
	protected static $cantUseWpUpdate = false;

	/** @var bool    Do I have an update that's less stable than my preferred stability? */
	protected static $stabilityError = false;

	/**
	 * Force WordPress to reload update information
	 *
	 * @return  void
	 * @since   1.6.3
	 */
	public static function forceReload(): void
	{
		delete_transient('update_plugins');
		delete_transient('admintoolswp_pluginupdate_frozenstate');
	}

	/**
	 * Report update information to WordPress.
	 *
	 * Handles the `pre_set_site_transient_update_plugins` filter
	 *
	 * Retrieve the update information from Admin Tools for WordPress' update cache and report them back to WordPress
	 * in a format it understands.
	 *
	 * The returned information is cached by WordPress and used by checkinfo() to render the Akeeba Backup for WordPress
	 * update information in WordPress' Plugins page.
	 *
	 * DO NOT TYPE HINT!
	 *
	 * @param   stdClass  $value
	 * @param   string    $transientName
	 *
	 * @return  stdClass
	 * @since   1.0.0
	 * @see     https://developer.wordpress.org/reference/hooks/pre_set_site_transient_transient/
	 */
	public static function getUpdateInformation($value = null, $transientName = null)
	{
		global $wp_version;

		// On WordPress < 4.3 we can't use the integrated update system since the hook we're using to tweak
		// the installation is not available (upgrader_package_options).
		// Let's warn the user and tell him to use our own update system
		if (version_compare($wp_version, '4.3', 'lt'))
		{
			static::$cantUseWpUpdate = true;
			self::freezeState();

			return $value;
		}

		static::loadAkeeba();

		// Dummy input class required for our models
		$input = new Input();

		// Do I have to notify the user that he needs to put the Download ID?
		$controlPanel = new ControlPanel($input);

		if ($controlPanel->needsDownloadID())
		{
			static::$needsDownloadID = true;
		}

		$updateInfo = false;

		try
		{
			$updateInfo = static::getUpdateInfo();
		}
		catch (ConnectionError $e)
		{
			// mhm... an error occurred while connecting to the updates server. Let's notify the user
			static::$connectionError = true;
		}
		catch (PlatformError $e)
		{
			static::$platformError = true;
		}
		catch (StabilityError $e)
		{
			static::$stabilityError = true;
		}

		self::freezeState();

		if (!$updateInfo)
		{
			return $value;
		}

		$dirSlug = self::getPluginSlug();

		$obj              = new stdClass();
		$obj->slug        = $dirSlug;
		$obj->plugin      = $dirSlug . '/admintoolswp.php';
		$obj->new_version = $updateInfo->get('version');
		$obj->url         = $updateInfo->get('infourl');
		$obj->package     = $updateInfo->get('link');

		if ($updateInfo->get('hasUpdate', false))
		{
			$value->response                                 = $value->response ?? [];
			$value->response[$dirSlug . '/admintoolswp.php'] = $obj;
		}
		else
		{
			$value->no_update                                 = $value->no_update ?? [];
			$value->no_update[$dirSlug . '/admintoolswp.php'] = $obj;

		}

		// Since the event we're hooking to is a global one (triggered for every plugin) we have to store a reference
		// of our download link. In this way we can apply our logic only on our stuff and don't interfere with other people
		static::$downloadLink = $updateInfo->get('link');

		return $value;
	}

	/**
	 * Used to render "View version x.x.x details" link from the plugins page.
	 *
	 * Handles the `plugins_api` filter.
	 *
	 * We hook to this event to redirect the connection from the WordPress directory to our site for updates
	 *
	 * DO NOT TYPE HINT!
	 *
	 * @param   false|object|array  $result  The result object or array. Default false.
	 * @param   string              $action  The type of information being requested from the Plugin Installation API.
	 * @param   object|array        $arg     Plugin API arguments.
	 *
	 * @return  false|object|array
	 * @since   1.2.0
	 *
	 * @see     https://developer.wordpress.org/reference/hooks/plugins_api/l
	 */
	public static function pluginInformationPage($result = false, $action = '', $arg = null)
	{
		if (!in_array($action ?: '', ['query_plugins', 'plugin_information']))
		{
			return $result;
		}

		if (!is_object($arg))
		{
			return $result;
		}

		$dirSlug = self::getPluginSlug();

		if (!isset($arg->slug))
		{
			return $result;
		}

		if ($arg->slug !== $dirSlug)
		{
			return $result;
		}

		static::loadAkeeba();

		try
		{
			$updateInfo = static::getUpdateInfo();
		}
		catch (\Exception $e)
		{
			$updateInfo = false;
		}

		// This should never occur, since if we get here, it means that we already have an update flagged
		if (!$updateInfo)
		{
			return $result;
		}

		/**
		 * This is the information WordPress is using to render the Admin Tools for WordPress row in its Plugins page.
		 */
		$information = [
			// We leave the "name" index empty, so WordPress won't display the ugly title on top of our banner
			'name'          => '',
			'slug'          => $dirSlug,
			'author'        => 'Akeeba Ltd.',
			'homepage'      => 'https://www.akeeba.com/products/admin-tools-wordpress.html',
			'last_updated'  => $updateInfo->get('date'),
			'version'       => $updateInfo->get('version'),
			'download_link' => $updateInfo->get('link'),
			'requires'      => '6.0',
			//'tested'        => get_bloginfo('version'),
			'sections'      => [
				'release_notes' => $updateInfo->get('releasenotes'),
			],
			'banners'       => [
				'low'  => plugins_url() . '/' . $dirSlug . '/app/media/images/wordpressupdate_admintools_banner.png',
				'high' => false,
			],
		];

		return (object) $information;
	}

	/**
	 * Throws an error if the Download ID is missing when the user tries to install an update.
	 *
	 * Handles the `upgrader_pre_download` filter.
	 *
	 * DO NOT TYPE HINT!
	 *
	 * @param   bool         $bailout
	 * @param   string       $package
	 * @param   WP_Upgrader  $upgrader
	 *
	 * @return  WP_Error|false  An error if anything goes wrong or is missing, either case FALSE to keep the update
	 *                          process going
	 *
	 * @return  void
	 * @since   1.2.0
	 * @see     https://developer.wordpress.org/reference/hooks/upgrader_pre_download/
	 */
	public static function addDownloadID($bailout = false, $package = null, $upgrader = null)
	{
		// Process only our download links
		if ($package != static::$downloadLink)
		{
			return false;
		}

		static::loadAkeeba();

		// Do we need the Download ID (ie Pro version)?
		$input        = new Input();
		$controlPanel = new ControlPanel($input);

		if ($controlPanel->needsDownloadID())
		{
			return new WP_Error(403, Language::_('COM_ADMINTOOLS_UPDATES_ERR_DOWNLOADID'));
		}

		// Our updater automatically sets the Download ID in the link, so there's no need to change anything inside the URL
		return false;
	}

	/**
	 * Helper function to display some custom text AFTER the row regarding our update.
	 *
	 * Handles the `after_plugin_row_akeebaebackupwp/akeebabackupwp.php` filter
	 *
	 * This is typically used to communicate problems preventing the update information from being retrieved or used,
	 * meaning updates for our plugin are essentially broken.
	 *
	 * DO NOT TYPEHINT!
	 *
	 * @param   string  $plugin_file  Path to the plugin file relative to the plugins directory.
	 * @param   array   $plugin_data  An array of plugin data.
	 * @param   string  $status       Status filter currently applied to the plugin list.
	 *
	 * @return  void
	 * @since   1.0.0
	 * @see     https://developer.wordpress.org/reference/hooks/after_plugin_row_plugin_file/
	 */
	public static function updateMessage($plugin_file = '', $plugin_data = [], $status = '')
	{
		// Shouldn't be needed, since other functions were called first, but let's be sure
		static::loadAkeeba();

		self::thawState();

		$html     = '';
		$warnings = [];

		if (static::$cantUseWpUpdate)
		{
			$warnings[] = '<p id="admintoolswp-error-update-cantuseintegrated">' . Language::_(
					'COM_ADMINTOOLS_UPDATES_ERR_CANTUSEWPUPDATE'
				) . '</p>';
		}
		elseif (static::$needsDownloadID)
		{
			$warnings[] = '<p id="admintoolswp-error-update-nodownloadid">' . Language::_(
					'COM_ADMINTOOLS_UPDATES_ERR_DOWNLOADID'
				) . '</p>';
		}
		elseif (static::$connectionError)
		{
			$warnings[] = '<p id="admintoolswp-error-update-noconnection">' . Language::_(
					'COM_ADMINTOOLS_UPDATES_ERR_CONNECTION'
				) . '</p>';
		}
		elseif (static::$platformError)
		{
			$warnings[] = '<p id="admintoolswp-error-update-platform-mismatch">' . Language::_(
					'COM_ADMINTOOLS_UPDATES_ERR_PLATFORM'
				) . '</p>';
		}
		elseif (static::$stabilityError)
		{
			/**
			 * There is an update available but it's less stable than the minimum stability preference.
			 *
			 * For example: a Beta is available but we are asked to only report stable versions.
			 *
			 * We deliberately don't show a warning. The whole point of the stability preference is to stop buggering
			 * the poor user during our pre-release runs (alphas, betas and occasional RC). In this case we just pretend
			 * there is no update available, just like we do in the interface of our plugin.
			 */
		}

		if ($warnings)
		{
			$warnings = implode('', $warnings);
			$msg      = Language::_('COM_ADMINTOOLS_LBL_COMMON_WARNING');

			$html = <<<HTML
<tr class="">
	<th></th>
	<td></td>
	<td>
		<div style="border: 1px solid #F0AD4E;border-radius: 3px;background: #fdf5e9;padding:10px">
			<strong>$msg</strong><br/>
			$warnings		
		</div>
	</td>
</tr>
HTML;
		}

		if ($html)
		{
			echo $html;
		}
	}

	/**
	 * Apply post-update code.
	 *
	 * Handles the `upgrader_process_complete` hook.
	 *
	 * After performing an update, let's invoke Admin Tools install method. That will take care of updating the database
	 * and any file "external" to the plugin folder (mu-plugin and auto-prepend file)
	 *
	 * @param   WP_Upgrader  $upgrader_object
	 * @param   array        $options
	 *
	 * @deprecated
	 * @return  void
	 * @since   1.2.0
	 * @see     https://developer.wordpress.org/reference/hooks/upgrader_process_complete/
	 */
	public static function postUpdate($upgrader_object = null, $options = [])
	{
		// Only handle update plugins
		if (!($options['action'] == 'update' && $options['type'] == 'plugin'))
		{
			return;
		}

		if (!isset($options['plugins']))
		{
			return;
		}

		foreach ($options['plugins'] as $plugin)
		{
			$dirSlug = self::getPluginSlug();

			if ($plugin != $dirSlug . '/admintoolswp.php')
			{
				continue;
			}

			require_once __DIR__ . '/installer.php';

			// This will take care of updating the MU plugin and the database
			AdminToolsInstaller::installOrUpdate();

			// Now I have to update the auto-prepend file
			AdminToolsInstaller::updateAutoPrependFile();

			break;
		}
	}

	/**
	 * Fetches the info from the remote server
	 *
	 * @return  Registry|null
	 * @since   1.0.0
	 */
	private static function getUpdateInfo()
	{
		static $updateInfo = null;

		static::loadAkeeba();

		// If I already have some update info, simply return them
		if ($updateInfo !== null)
		{
			return $updateInfo;
		}

		$input       = new Input();
		$updateModel = new Update($input);

		$updateModel->load(true);

		$updateInfo = $updateModel->getUpdateInformation();

		// No updates? Let's stop here
		if (!$updateModel->hasUpdate())
		{
			// Did we get a connection error?
			if ($updateInfo->get('loadedUpdate') == false)
			{
				throw new ConnectionError();
			}

			// We might have an update that does not match the stability preference, e.g. RC with min. stability Stable.
			if ($updateInfo->get('minstabilityMatch') == false)
			{
				throw new StabilityError();
			}

			// mhm... maybe we're on a old WordPress version?
			if (!$updateInfo->get('platformMatch', 0))
			{
				throw new PlatformError();
			}
		}

		return $updateInfo;
	}

	/**
	 * Set up our autoloader
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private static function loadAkeeba()
	{
		static $loaded;

		if ($loaded)
		{
			return;
		}

		$dirSlug = self::getPluginSlug();

		if (!defined('ADMINTOOLSWP_PATH'))
		{
			define('ADMINTOOLSWP_PATH', realpath(WP_PLUGIN_DIR . '/' . $dirSlug));
		}

		require_once ADMINTOOLSWP_PATH . '/app/library/autoloader/autoloader.php';

		Akeeba\AdminTools\Library\Autoloader\Autoloader::getInstance()->addMap(
			'Akeeba\AdminTools\Admin\\', [ADMINTOOLSWP_PATH . '/app']
		);

		$loaded = true;
	}

	/**
	 * Freeze the update warnings state.
	 *
	 * We create an array with the update warnings flags and save it as a site transient.
	 *
	 * @return  void
	 * @since   1.2.0
	 */
	private static function freezeState()
	{
		$frozenState = [];

		foreach (self::STATE_KEYS as $key)
		{
			if (isset(self::${$key}))
			{
				$frozenState[$key] = self::${$key};
			}
		}

		set_site_transient('admintoolswp_pluginupdate_frozenstate', $frozenState);
	}

	/**
	 * Unfreeze the update warnings state.
	 *
	 * We read the site transient and restore the update warnings flags from it, if it's set.
	 *
	 * @return  void
	 * @since   1.2.0
	 */
	private static function thawState()
	{
		$frozenState = get_site_transient('admintoolswp_pluginupdate_frozenstate');

		if (empty($frozenState) || !is_array($frozenState))
		{
			return;
		}

		foreach (self::STATE_KEYS as $key)
		{
			if (isset(self::${$key}) && isset($frozenState[$key]))
			{
				self::${$key} = $frozenState[$key];
			}
		}
	}

	/**
	 * Returns the subdirectory of the main WP_CONTENT_DIR/plugins folder where our plugin is installed.
	 *
	 * @return  string
	 * @since   1.6.3
	 */
	private static function getPluginSlug(): string
	{
		$pluginsUrl   = plugins_url('', realpath(__DIR__ . '/../admintoolswp.php'))
			?: realpath(__DIR__ . '/..');
		$baseUrlParts = explode('/', $pluginsUrl);
		$dirSlug      = end($baseUrlParts);

		if (!empty($dirSlug) && ($dirSlug != '..'))
		{
			return $dirSlug;
		}

		$fullDir  = __DIR__;
		$dirParts = explode(DIRECTORY_SEPARATOR, $fullDir, 3);
		$dirSlug  = $dirParts[1] ?? 'admintoolswp';

		return $dirSlug;
	}

}
