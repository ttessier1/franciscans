<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Engine\Factory;
use Awf\Container\Container;
use Awf\Database\Installer;
use Solo\Model\Cron;
use Solo\Model\Main;

/**
 * @package        akeebabackupwp
 * @copyright      Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU GPL version 3 or later
 */
class AkeebaBackupWP
{
	/** @var string The name of the wp-content/plugins directory we live in */
	public static $dirName = 'akeebabackupwp';

	/** @var string The name of the main plugin file */
	public static $fileName = 'akeebabackupwp.php';

	/** @var string Absolute filename to self */
	public static $absoluteFileName = null;

	/**
	 * @var string
	 */
	public static $pluginUrl;

	/** @var bool Do we have an outdated PHP version? */
	public static $wrongPHP = false;

	/**
	 * The plugin basename, used for detecting the plugin having been updated
	 *
	 * @var   string
	 * @since 8.0.0
	 */
	public static $pluginBaseName = '';

	/** @var string Minimum PHP version */
	public static $minimumPHP = '7.4.0';

	/**
	 * @var array Application configuration, read from helpers/private/config.php
	 */
	public static $appConfig = null;

	protected static $loadedScripts = [];

	/**
	 * The application container
	 *
	 * @var   \Solo\Container|null
	 * @since 8.1.0
	 */
	private static $container = null;

	/**
	 * Initialization, runs once when the plugin is loaded by WordPress
	 *
	 * @param   string  $pluginFile  The absolute path of the plugin file being loaded
	 *
	 * @return  void
	 */
	public static function initialization(string $pluginFile): void
	{
		if (defined('AKEEBABACKUPWP_PATH'))
		{
			return;
		}

		$pluginUrl    = plugins_url('', $pluginFile);
		$baseUrlParts = explode('/', $pluginUrl);

		self::$minimumPHP = defined('AKEEBABACKUP_MINPHP') ? AKEEBABACKUP_MINPHP : '7.4.0';
		self::$pluginUrl        = $pluginUrl;
		self::$dirName          = end($baseUrlParts);
		self::$fileName         = basename($pluginFile);
		self::$absoluteFileName = $pluginFile;
		self::$wrongPHP         = version_compare(PHP_VERSION, AkeebaBackupWP::$minimumPHP, 'lt');

		if (!defined('AKEEBABACKUPWP_PATH'))
		{
			define('AKEEBABACKUPWP_PATH', plugin_dir_path($pluginFile));
		}

		if (!defined('AKEEBABACKUPWP_ROOTURL'))
		{
			define('AKEEBABACKUPWP_ROOTURL', site_url());
		}

		if (!defined('AKEEBABACKUPWP_URL'))
		{
			define(
				'AKEEBABACKUPWP_URL',
				admin_url() . (is_multisite() ? 'network/' : '') . 'admin.php?page=' .
				self::$dirName . '/' . self::$fileName
			);
		}

		if (!defined('AKEEBABACKUPWP_SITEURL'))
		{
			$baseUrl = plugins_url('app/index.php', self::$absoluteFileName);
			define('AKEEBABACKUPWP_SITEURL', substr($baseUrl, 0, -10));
		}

		if (!defined('AKEEBABACKUP_VERSION'))
		{
			$versionFile = dirname(self::$absoluteFileName) . '/app/version.php';

			if (@is_file($versionFile) && @is_readable($versionFile))
			{
				@include_once $versionFile;
			}
		}

		defined('AKEEBABACKUP_PRO')
		|| define(
			'AKEEBABACKUP_PRO',
			@is_dir(__DIR__ . '/../app/Solo/AliceChecks') ? '1' : '0'
		);
		defined('AKEEBABACKUP_VERSION') || define('AKEEBABACKUP_VERSION', '0.0.0.a1');
		defined('AKEEBABACKUP_DATE') || define('AKEEBABACKUP_DATE', gmdate('Y-m-d'));
		defined('AKEEBABACKUP_MINPHP') || define('AKEEBABACKUP_MINPHP', self::$minimumPHP);

		self::storeUnquotedRequest();
	}

	/**
	 * Load the WordPress plugin updater integration, unless the `integratedupdate` flag in the configuration is unset.
	 * The default behavior is to add the integration.
	 *
	 * @return void
	 */
	public static function loadIntegratedUpdater()
	{
		if (is_null(self::$appConfig))
		{
			self::loadAppConfig();
		}

		if (
			!is_array(self::$appConfig['options'] ?? null)
			|| (self::$appConfig['options']['integratedupdate'] ?? 1) == 0
		)
		{
			return;
		}

		self::$pluginBaseName = plugin_basename(self::$absoluteFileName);

		add_filter(
			'pre_set_site_transient_update_plugins',
			['AkeebaBackupWPUpdater', 'getUpdateInformation'],
			10, 2
		);
		add_filter(
			'plugins_api',
			['AkeebaBackupWPUpdater', 'pluginInformationPage'],
			10, 3
		);
		add_filter(
			'upgrader_pre_download',
			['AkeebaBackupWPUpdater', 'addDownloadID'],
			10, 3
		);
		add_filter(
			'after_plugin_row_' . self::$pluginBaseName,
			['AkeebaBackupWPUpdater', 'updateMessage'], 10, 3
		);
	}

	/**
	 * Installs the plugin hooks.
	 *
	 * This is what makes the Akeeba Backup plugin tick. It is wrapped in its own method for easier error control.
	 *
	 * @return  void
	 * @since   8.1.2
	 */
	public static function installHooks(string $pluginFile): void
	{
		/**
		 * Register public plugin hooks
		 */
		register_activation_hook(self::$absoluteFileName, ['AkeebaBackupWP', 'install']);

		/**
		 * Register public plugin deactivation hooks
		 *
		 * This is called when the plugin is deactivated which precedes (but does not necessarily imply) uninstallation.
		 */
		register_deactivation_hook(self::$absoluteFileName, ['AkeebaBackupWP', 'onDeactivate']);
		register_uninstall_hook(self::$absoluteFileName, ['AkeebaBackupWP', 'uninstall']);

		/**
		 * Register administrator plugin hooks
		 */
		if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX))
		{
			// Menu items
			add_action('admin_menu', ['AkeebaBackupWP', 'adminMenu']);
			add_action('network_admin_menu', ['AkeebaBackupWP', 'networkAdminMenu']);

			// Output buffering, wherever it is needed
			add_action('init', ['AkeebaBackupWP', 'startOutputBuffering'], 1);
			add_action('in_admin_footer', ['AkeebaBackupWP', 'stopOutputBuffering']);

			add_action('init', ['AkeebaBackupWP', 'loadCommonCSS'], 1);
			add_action('clear_auth_cookie', ['AkeebaBackupWP', 'onUserLogout'], 1);
		}
		elseif (defined('DOING_AJAX') && DOING_AJAX)
		{
			add_action('wp_ajax_akeebabackup_api', ['AkeebaBackupWP', 'jsonApi'], 1);
			add_action('wp_ajax_nopriv_akeebabackup_api', ['AkeebaBackupWP', 'jsonApi'], 1);

			add_action('wp_ajax_akeebabackup_legacy', ['AkeebaBackupWP', 'legacyFrontendBackup'], 1);
			add_action('wp_ajax_nopriv_akeebabackup_legacy', ['AkeebaBackupWP', 'legacyFrontendBackup'], 1);

			add_action('wp_ajax_akeebabackup_check', ['AkeebaBackupWP', 'frontendBackupCheck'], 1);
			add_action('wp_ajax_nopriv_akeebabackup_check', ['AkeebaBackupWP', 'frontendBackupCheck'], 1);
		}

		// PseudoCRON with WP-CRON
		// -- Add an "every ten seconds" interval rule (schedule)
		add_filter('cron_schedules', function ($schedules) {
			$interval = max(defined('WP_CRON_LOCK_TIMEOUT') ? WP_CRON_LOCK_TIMEOUT : 60, 10);

			$schedules['akeebabackup_interval'] = [
				'interval' => $interval,
				'display'  => sprintf(__('Every %s seconds'), $interval),
			];

			return $schedules;
		});

		// -- Register the abwp_cron_scheduling action
		add_action('abwp_cron_scheduling', ['AkeebaBackupWP', 'handlePseudoCron']);
		// -- Make sure the abwp_cron_scheduling action is scheduled to run once every 10 seconds
		if (!wp_next_scheduled('abwp_cron_scheduling'))
		{
			wp_schedule_event(time(), 'akeebabackup_interval', 'abwp_cron_scheduling');
		}

		// Register WP-CLI commands
		if (defined('WP_CLI') && WP_CLI)
		{
			if (file_exists(dirname(self::$absoluteFileName) . '/wpcli/register_commands.php'))
			{
				require_once dirname(self::$absoluteFileName) . '/wpcli/register_commands.php';
			}
		}
	}

	/**
	 * Starts output bufferring, if necessary
	 */
	public static function startOutputBuffering(): void
	{
		global $AKEEBABACKUPWP_REAL_REQUEST;

		$ourPluginPage = self::$dirName . '/' . self::$fileName;
		$requestPage   = $AKEEBABACKUPWP_REAL_REQUEST['page'] ?? null;
		$format        = $AKEEBABACKUPWP_REAL_REQUEST['format'] ?? 'html';
		$tmpl          = $AKEEBABACKUPWP_REAL_REQUEST['tmpl'] ?? 'index';

		// If this is not a page in our plugin, or we've already started the output buffering, bail out.
		if (defined('AKEEBABACKUPWP_OBFLAG') || $requestPage !== $ourPluginPage)
		{
			return;
		}

		// We only need output buffering in very specific situations
		if ($format !== 'raw' && $format !== 'json' && $tmpl !== 'component')
		{
			return;
		}

		define('AKEEBABACKUPWP_OBFLAG', 1);
		@ob_start();
	}

	/**
	 * Stop the output buffering, when necessary
	 */
	public static function stopOutputBuffering(): void
	{
		if (!defined('AKEEBABACKUPWP_OBFLAG'))
		{
			return;
		}

		@ob_end_clean();
		exit(0);
	}

	/**
	 * Preload our common CSS files.
	 *
	 * We have to do that to prevent an ugly flash of the page since, by default, WordPress adds the CSS to the
	 * footer (right above the closing body tag). This would cause the browser to re-evaluate the stylesheet,
	 * causing the flash.
	 */
	public static function loadCommonCSS(): void
	{
		$ourPluginPage = self::$dirName . '/' . self::$fileName;
		$requestPage   = $AKEEBABACKUPWP_REAL_REQUEST['page'] ?? null;

		// Is this a page of our plugin?
		if ($requestPage !== $ourPluginPage)
		{
			return;
		}

		$styleSheets = ['fef-wp', 'theme'];
		$relPath     = __DIR__ . '/../';

		self::loadAppConfig();

		if ((self::$appConfig['darkmode'] ?? 0) == 1)
		{
			$styleSheets[] = 'dark';
		}

		foreach ($styleSheets as $style)
		{
			$scriptPath = 'app/media/css/' . $style . '.min.css';

			if (!file_exists($relPath . $scriptPath))
			{
				continue;
			}

			AkeebaBackupWP::enqueueStyle(plugins_url($scriptPath, self::$absoluteFileName));
		}
	}

	/**
	 * Installation hook.
	 *
	 * Creates the database tables if they do not exist and performs any post-installation work required.
	 */
	public static function install(): void
	{
		self::$dirName = self::getPluginSlug();

		// Require WordPress 6.0 or later
		if (version_compare(get_bloginfo('version'), '6.0', 'lt'))
		{
			deactivate_plugins(self::$fileName);
		}

		$container = self::loadAkeebaBackupContainer();

		if ($container)
		{
			/** @var Main $cpanelModel */
			$cpanelModel = $container->mvcFactory->makeModel('Main');

			try
			{
				$cpanelModel->checkAndFixDatabase(false);
			}
			catch (Throwable $e)
			{
				// The update is stuck. We will display a warning in the Control Panel
				@ob_end_clean();
				echo <<< HTML
<h1>Plugin activation failed</h1>
<p>
	The Akeeba Backup plugin failed to activate because the database server did not allow the database tables to be installed or updated. You will need to contact our support.
</p>
<h2>
	Technical information
</h2>
<p>
	<code>{$e->getCode()}</code> &mdash; {$e->getMessage()}
</p>
<pre>{$e->getTraceAsString()}</pre>
HTML;
				die;
			}

			update_option('akeebabackupwp_plugin_dir', self::$dirName);

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

			@copy(
				WP_PLUGIN_DIR . '/' . self::$dirName . '/helpers/assets/mu-plugins/akeeba-backup-coreupdate.php',
				$mu_folder . '/akeeba-backup-coreupdate.php'
			);
		}

		// Register the uninstallation hook
		register_uninstall_hook(self::$absoluteFileName, ['AkeebaBackupWP', 'uninstall']);
	}

	/**
	 * Plugin deactivation hook handler.
	 *
	 * This precedes (but does not necessarily imply) uninstallation. A deactivated plugin can be reactivated at any
	 * time. This used solely to clean up temporary data, such as WP-CRON hooks.
	 *
	 * @return  void
	 * @since   7.8.0
	 */
	public static function onDeactivate(): void
	{
		// Unregister the CRON handler
		$timestamp = wp_next_scheduled('abwp_cron_scheduling');

		if ($timestamp)
		{
			wp_unschedule_event($timestamp, 'abwp_cron_scheduling');
		}
	}

	/**
	 * Uninstallation hook
	 *
	 * Removes database tables if they exist and performs any post-uninstallation work required.
	 *
	 * @return  void
	 */
	public static function uninstall(): void
	{
		$container = self::loadAkeebaBackupContainer();

		if ($container)
		{
			$dbInstaller = new Installer($container);
			$dbInstaller->removeSchema();
		}

		// Delete the must-use plugin files
		$mu_folder = ABSPATH . 'wp-content/mu-plugins';

		if (defined('WPMU_PLUGIN_DIR') && WPMU_PLUGIN_DIR)
		{
			$mu_folder = WPMU_PLUGIN_DIR;
		}

		@unlink($mu_folder . '/akeeba-backup-coreupdate.php');
	}

	/**
	 * Create the administrator menu for Akeeba Backup
	 */
	public static function adminMenu(): void
	{
		if (is_multisite())
		{
			return;
		}

		$container = self::loadAkeebaBackupContainer();

		if (!$container)
		{
			return;
		}

		if ($container->appConfig->get('under_tools', 0) == 1)
		{
			add_management_page(
				'Akeeba Backup', 'Akeeba Backup', 'edit_others_posts',
				self::$absoluteFileName, ['AkeebaBackupWP', 'bootApplication']
			);
		}
		else
		{
			add_menu_page(
				'Akeeba Backup', 'Akeeba Backup', 'edit_others_posts',
				self::$absoluteFileName, ['AkeebaBackupWP', 'bootApplication'],
				plugins_url('app/media/logo/abwp-24-white.png', self::$absoluteFileName)
			);
		}
	}

	/**
	 * Create the blog network administrator menu for Akeeba Backup
	 */
	public static function networkAdminMenu(): void
	{
		if (!is_multisite())
		{
			return;
		}

		add_menu_page(
			'Akeeba Backup', 'Akeeba Backup', 'manage_options',
			self::$absoluteFileName, ['AkeebaBackupWP', 'bootApplication'],
			plugins_url('app/media/logo/abwp-24-white.png', self::$absoluteFileName)
		);
	}

	/**
	 * Boots the Akeeba Backup application
	 *
	 * @param   string  $bootstrapFile  The name of the application bootstrap file to use.
	 */
	public static function bootApplication(string $bootstrapFile = 'boot_webapp.php'): void
	{
		$bootstrapFile = $bootstrapFile ?: 'boot_webapp.php';

		if (self::$wrongPHP)
		{
			echo sprintf(
				'Akeeba Backup for WordPress requires PHP %s or later. Your site is currently using PHP %s',
				self::$minimumPHP,
				PHP_VERSION
			);

			return;
		}

		$strapFile = dirname(self::$absoluteFileName) . '/helpers/' . $bootstrapFile;

		if (!file_exists($strapFile))
		{
			die("Oops! Cannot initialize Akeeba Backup. Cannot locate the file $strapFile");
		}

		$container = self::loadAkeebaBackupContainer();

		if (!$container)
		{
			return;
		}

		include_once $strapFile;
	}

	/**
	 * Enqueues a Javascript file for loading
	 *
	 * @param   string  $url  The URL of the Javascript file to load
	 */
	public static function enqueueScript(string $url): void
	{
		$parts = explode('?', $url);
		$url   = $parts[0];

		if (in_array($url, self::$loadedScripts))
		{
			return;
		}

		self::$loadedScripts[] = $url;

		$handle = 'akjs' . md5($url);

		wp_enqueue_script($handle, $url, [], self::getMediaVersion(), false);
	}

	/**
	 * Enqueues an inline Javascript script
	 *
	 * @param   string  $content  The script contents
	 */
	public static function enqueueInlineScript(string $content): void
	{
		/**
		 * WordPress only adds inline scripts as "extra data" of an already queued script file. Since we want to add our
		 * inline scripts **after** our script files we find the handle of the last script file we queued and add the
		 * inline script to it.
		 *
		 * This means that this method will only really work correctly if it's called AFTER the last self::enqueueScript
		 * call.
		 */
		$url = end(self::$loadedScripts);

		$handle = 'akjs' . md5($url);

		wp_add_inline_script($handle, $content);
	}

	/**
	 * Enqueues a CSS file for loading
	 *
	 * @param   string  $url  The URL of the CSS file to load
	 */
	public static function enqueueStyle(string $url): void
	{
		if (!defined('AKEEBABACKUP_VERSION'))
		{
			@include_once dirname(self::$absoluteFileName) . '/app/version.php';
		}

		$handle = 'akcss' . md5($url);

		wp_enqueue_style($handle, $url, [], self::getMediaVersion());
	}

	/**
	 * Runs when the authentication cookie is being cleared (user logs out)
	 *
	 * @return  void
	 */
	public static function onUserLogout(): void
	{
		// Remove the user meta which are used in our fake session handler
		$userId  = get_current_user_id();
		$allMeta = get_user_meta($userId);

		foreach ($allMeta ?: [] as $key => $value)
		{
			if (strpos($key, 'AkeebaSession_') !== 0)
			{
				continue;
			}

			delete_user_meta($userId, $key);
		}
	}

	/**
	 * Returns the backup profile that should be used on Manual WordPress update.
	 *
	 * Returns NULL if we don't want to take a backup
	 *
	 * @return int|null
	 */
	public static function getProfileManualCoreUpdate(): ?int
	{
		if (is_null(self::$appConfig))
		{
			self::loadAppConfig();
		}

		$isPro = defined('AKEEBABACKUP_PRO') ? AKEEBABACKUP_PRO : 0;

		if (!$isPro)
		{
			return null;
		}

		// If the option has been set, and it's false, let's stop. Otherwise, continue (enabled by default)
		if (!(self::$appConfig['options']['backuponupdate_core_manual'] ?? false))
		{
			return null;
		}

		// Default backup profile is 1
		$profile = self::$appConfig['options']['backuponupdate_core_manual_profile'] ?? 1;

		if ($profile <= 0)
		{
			return null;
		}

		return (int) $profile;
	}

	/**
	 * Includes all the required pieces to load Akeeba Backup from within a standard WordPress page
	 *
	 * @return \Solo\Container|null
	 */
	public static function loadAkeebaBackupContainer()
	{
		if (self::$container)
		{
			return self::$container;
		}

		// Load the basics
		self::$dirName = self::getPluginSlug();

		defined('AKEEBASOLO') || define('AKEEBASOLO', 1);

		if (!file_exists(__DIR__ . '/../helpers/integration.php'))
		{
			return null;
		}

		/** @var \Solo\Container $container */
		self::$container = require __DIR__ . '/../helpers/integration.php';

		self::$container = self::$container ?: null;

		if (!self::$container)
		{
			return null;
		}

		// Since the Platform is already loaded at this point, we can tell it to use the correct key file
		Factory::getSecureSettings()->setKeyFilename(
			rtrim(
				(defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : (rtrim(ABSPATH, '/') . '/wp-content')),
				'/'
			) . '/akeebabackup_secretkey.php'
		);

		// Get all info saved inside the configuration
		self::$container->appConfig->loadConfiguration();
		self::$container->basePath = realpath(__DIR__ . '/../app/Solo');

		if (!@is_dir(self::$container->basePath))
		{
			self::$container->basePath = WP_PLUGIN_DIR . '/akeebabackupwp/app/Solo';
		}

		// Make sure post-upgrade code has executed before doing anything else!
		try
		{
			/** @var Main $mainModel */
			$mainModel = self::$container->mvcFactory->makeTempModel('Main');
			$mainModel->postUpgradeActions(true);
		}
		catch (Exception $e)
		{
			// The post-upgrade code failed. All bets are off!
		}

		return self::$container;
	}

	/**
	 * Issues a redirection to the 'installation' folder if such a folder is present and seems to contain a copy of
	 * ANGIE. This prevents some webmasters used to the Stone Ages from unzipping a backup archive and not running the
	 * installer, then complain very loudly that Akeeba Backup doesn't work when the only thing doesn't working is their
	 * common sense.
	 *
	 * In simple terms, this static method fixes stupid.
	 */
	public static function redirectIfInstallationPresent()
	{
		$installDir   = rtrim(ABSPATH, '/\\') . '/installation';
		$installIndex = rtrim(ABSPATH, '/\\') . '/installation/index.php';

		if (!@is_dir($installDir) && !is_file($installIndex))
		{
			return;
		}

		$indexContents = @file_get_contents($installIndex);

		if ($indexContents === false)
		{
			return;
		}

		if (!preg_match('#\s*\*\s*ANGIE\s#', $indexContents) || (strpos($indexContents, '_AKEEBA') === false))
		{
			return;
		}

		ob_end_clean();
		ob_start();

		try
		{
			// Required by the integration.php file
			defined('AKEEBASOLO') || define('AKEEBASOLO', 1);
			// Creates the application container, required for translations to work
			/** @var Container $container */
			$container = require 'integration.php';
			// This tells AWF to consider the 'solo' app as the default
			$app = Awf\Application\Application::getInstance($container->application->getName());
			// Tell the app to load the translation strings
			$app->initialise();
			// Load the message page
			require __DIR__ . '/installation_detected.php';
			// Show the message page
			ob_end_flush();
		}
		catch (Exception $e)
		{
			// If something broke we show a low-tech, abbreviated page
			ob_end_clean();

			echo <<< HTML
<html>
<head><title>You have not completed the restoration of this site backup</title></head>
<body>
<h1>You have not completed the restoration of this site backup</h1>
<p>
	Please <a href="installation/index.php">click here</a> to run the restoration script. Do remember to delete the
	<code>installation</code> directory after you are done restoring your site to prevent this page from appearing
	again. 
</p>
</body>
</html>
HTML;
			die;
		}

		exit(200);
	}

	/**
	 * New JSON API entry point.
	 *
	 * You can access it as /wp-admin/admin-ajax.php?action=akeebabackup_api
	 *
	 * @return  void
	 * @since   7.7.0
	 */
	public static function jsonApi()
	{
		// Make sure the application configuration has been loaded
		if (is_null(self::$appConfig))
		{
			self::loadAppConfig();
		}

		// Get the container
		$container = self::loadAkeebaBackupContainer();

		if (!$container)
		{
			return;
		}

		// This is necessary to load the language files
		$container->application->initialise();
		// tell it this is the Api view and execute Akeeba Backup.
		$container->input->set('view', 'api');
		$container->dispatcher->dispatch();
	}

	/**
	 * New Legacy Frontend Backup entry point.
	 *
	 * You can access it as /wp-admin/admin-ajax.php?action=akeebabackup_legacy
	 *
	 * @return  void
	 * @since   7.7.0
	 */
	public static function legacyFrontendBackup()
	{
		// Make sure the application configuration has been loaded
		if (is_null(self::$appConfig))
		{
			self::loadAppConfig();
		}

		// Get the container
		$container = self::loadAkeebaBackupContainer();

		if (!$container)
		{
			return;
		}

		// This is necessary to load the language files
		$container->application->initialise();
		// Tell it this is the Remote view and execute Akeeba Backup.
		$container->input->set('view', 'remote');
		$container->dispatcher->dispatch();
	}

	/**
	 * New Frontend Backup Check entry point.
	 *
	 * You can access it as /wp-admin/admin-ajax.php?action=akeebabackup_check
	 *
	 * @return  void
	 * @since   7.7.0
	 */
	public static function frontendBackupCheck()
	{
		// Make sure the application configuration has been loaded
		if (is_null(self::$appConfig))
		{
			self::loadAppConfig();
		}

		// Get the container
		$container = self::loadAkeebaBackupContainer();

		if (!$container)
		{
			return;
		}

		// This is necessary to load the language files
		$container->application->initialise();
		// Tell it this is the Api view and execute Akeeba Backup.
		$container->input->set('view', 'check');
		$container->dispatcher->dispatch();
	}

	/**
	 * Handle pseudo-CRON
	 *
	 * @return void
	 * @since  7.8.0
	 */
	public static function handlePseudoCron()
	{
		// Make sure the application configuration has been loaded
		if (is_null(self::$appConfig))
		{
			self::loadAppConfig();
		}

		// Get the application container
		$container = self::loadAkeebaBackupContainer();

		if (!$container)
		{
			return;
		}

		// This feature is only available in the Professional version
		$isPro = defined('AKEEBABACKUP_PRO') ? AKEEBABACKUP_PRO : 0;

		if (!$isPro)
		{
			return null;
		}

		/** @var Cron $model */
		$model = $container->mvcFactory->makeTempModel('Cron');
		$model->runNextTask();
	}

	/**
	 * Store the unquoted request variables to prevent WordPress from killing JSON requests.
	 */
	private static function storeUnquotedRequest(): void
	{
		// See http://stackoverflow.com/questions/8949768/with-magic-quotes-disabled-why-does-php-wordpress-continue-to-auto-escape-my
		global $AKEEBABACKUPWP_REAL_REQUEST;

		if (!empty($AKEEBABACKUPWP_REAL_REQUEST))
		{
			return;
		}

		/**
		 * Some very misguided web hosts set request_order = "" in the php.ini. As a result, the $_REQUEST superglobal
		 * is not set at all. Since ini_get is not realiably available on these hosts we have to check for that
		 * condition in an oblique way and work around it if needed.
		 */
		$AKEEBABACKUPWP_REAL_REQUEST = (empty($_REQUEST) && (!empty($_GET) || !empty($_POST))) ? array_merge_recursive(
			$_GET, $_POST
		)
			: array_merge($_REQUEST, []);
	}

	/**
	 * Get the value for the media version query string.
	 *
	 * @return  string
	 */
	private static function getMediaVersion()
	{
		// The media version is cached for performance reasons
		static $mediaVersion;

		if (!empty($mediaVersion))
		{
			return $mediaVersion;
		}

		// Get a per-site key to scramble the software version: the size of this file plus its modification time
		$filesize  = @filesize(__FILE__) ?: 0;
		$filemtime = @filemtime(__FILE__) ?: 0;
		$key       = sprintf("%d@%s", $filesize, $filemtime);

		/**
		 * If WordPress debug is enabled add a per-request element to the key, guaranteeing an ever-changing media
		 * version which prevents the browser from ever caching the media files of this plugin. This is useful in
		 * development.
		 */
		if (defined('WP_DEBUG') && WP_DEBUG)
		{
			$key .= ':' . microtime(true);
		}

		// At the very least use a simple MD5 hash as the media version
		$mediaVersion = md5(
			(defined('AKEEBABACKUP_VERSION') ? AKEEBABACKUP_VERSION : '') . ':' . $key
		);

		// If possible, use HMAC-MD5 which makes it harder to deduce the plugin version just from the media version.
		if (function_exists('hash_hmac'))
		{
			$mediaVersion = hash_hmac(
				'md5',
				defined('AKEEBABACKUP_VERSION') ? AKEEBABACKUP_VERSION : '',
				$key
			);
		}

		return $mediaVersion;
	}

	private static function loadAppConfig()
	{
		self::$appConfig = [];

		$container = self::loadAkeebaBackupContainer();

		if (!$container)
		{
			return;
		}

		try
		{
			$config = @json_decode($container->appConfig->toString('JSON'), true);
		}
		catch (Exception $e)
		{
			return;
		}

		if (!is_array($config))
		{
			return;
		}

		self::$appConfig = $config;
	}

	private static function getPluginSlug(): string
	{
		$pluginsUrl   = plugins_url('', realpath(__DIR__ . '/../akeebabackupwp.php')) ?: realpath(__DIR__ . '/..');
		$baseUrlParts = explode('/', $pluginsUrl);
		$dirSlug      = end($baseUrlParts);

		if (!empty($dirSlug) && ($dirSlug != '..'))
		{
			return $dirSlug;
		}

		$fullDir  = __DIR__;
		$dirParts = explode(DIRECTORY_SEPARATOR, $fullDir, 3);
		$dirSlug  = $dirParts[1] ?? 'akeebabackup';

		return $dirSlug;
	}
}

call_user_func(
	function () {
		if (!defined('WPINC'))
		{
			return;
		}

		$filePath = @realpath(__DIR__ . '/../akeebabackupwp.php');

		if (empty($filePath) || basename($filePath) !== 'akeebabackupwp.php')
		{
			return;
		}

		AkeebaBackupWP::initialization($filePath);
	}
);