<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Date\Date;

/**
 * @package        admintoolswp
 * @copyright      Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU GPL version 3 or later
 */
class AdminToolsWP
{
	/** @var string The name of the wp-content/plugins directory we live in */
	public static $dirName = 'admintoolswp';

	/** @var string The name of the main plugin file */
	public static $fileName = 'admintoolswp.php';

	/** @var string Absolute filename to self */
	public static $absoluteFileName = null;

	/** @var string Name of our menu page. Used for saving screen options */
	protected static $menu_page = '';

	public static function initialization(string $pluginFile): void
	{
		$baseUrlParts = explode('/', plugins_url('', $pluginFile));

		AdminToolsWP::$dirName          = end($baseUrlParts);
		AdminToolsWP::$fileName         = basename($pluginFile);
		AdminToolsWP::$absoluteFileName = $pluginFile;

		if (!defined('ADMINTOOLSWP_PATH'))
		{
			define('ADMINTOOLSWP_PATH', WP_PLUGIN_DIR . '/' . AdminToolsWP::$dirName);
		}

		if (file_exists(ADMINTOOLSWP_PATH . '/version.php'))
		{
			require_once(ADMINTOOLSWP_PATH . '/version.php');
		}

		// Constant used for loading media assets
		define('ADMINTOOLSWP_MEDIAURL', plugins_url('', $pluginFile));
	}

	/**
	 * Store the unquoted request variables to prevent WordPress from killing JSON requests.
	 */
	public static function fakeRequest()
	{
		// See http://stackoverflow.com/questions/8949768/with-magic-quotes-disabled-why-does-php-wordpress-continue-to-auto-escape-my
		global $_REAL_REQUEST;
		$_REAL_REQUEST = $_REQUEST;
	}

	/**
	 * Installation hook. Creates the database tables if they do not exist and performs any post-installation work
	 * required.
	 */
	public static function install()
	{
		require_once __DIR__ . '/installer.php';

		AdminToolsInstaller::installOrUpdate();

		register_uninstall_hook(__FILE__, ['AdminToolsWP', 'uninstall']);
	}

	/**
	 * Uninstallation hook
	 *
	 * Removes database tables if they exist and performs any post-uninstallation work required.
	 */
	public static function uninstall()
	{
		require_once __DIR__ . '/installer.php';

		AdminToolsInstaller::uninstall();
	}

	/**
	 * Runs when the plugin is deactivated
	 */
	public static function deactivate()
	{
		include_once dirname(self::$absoluteFileName) . '/helpers/bootstrap.php';

		$storage = Storage::getInstance();

		// Are we allowed to deactivate ourselves?
		if ($storage->getValue('selfprotect', 0))
		{
			die(Language::_('COM_ADMINTOOLS_SELFPROTECT_ERRMSG'));
		}
	}

	/**
	 * Register our custom interval of 5 minutes
	 *
	 * @param   array  $schedules
	 *
	 * @return  array
	 */
	public static function cron_interval($schedules)
	{
		if (!isset($schedules['five_min']))
		{
			$schedules['five_min'] = [
				'interval' => 5 * 60,
				'display'  => esc_html__('Every Five Minutes'),
			];
		}

		return $schedules;
	}

	/**
	 * Runs time-based tasks (ie sending queued emails)
	 */
	public static function cron()
	{
		include_once dirname(self::$absoluteFileName) . '/helpers/bootstrap.php';

		$db = Wordpress::getDb();

		$query  = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__admintools_storage'))
			->where($db->qn('at_key') . ' LIKE ' . $db->q('mail_%', false));
		$emails = $db->setQuery($query)->loadObjectList();

		foreach ($emails as $email)
		{
			$data = json_decode($email->at_value);

			// Sanity checks
			if (!isset($data->recipients) || !isset($data->subject) || !isset($data->body))
			{
				continue;
			}

			// Delete the email from the queue only if it was correctly sent
			if (Wordpress::sendEmail($data->recipients, $data->subject, $data->body, false, $data->html))
			{
				$query = $db->getQuery(true)
					->delete($db->qn('#__admintools_storage'))
					->where($db->qn('at_key') . ' = ' . $db->q($email->at_key));

				$db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Runs time-based tasks (once every hour)
	 */
	public static function cronHourly()
	{
		include_once dirname(self::$absoluteFileName) . '/helpers/bootstrap.php';

		// Disable Temp users
		if (class_exists('Akeeba\AdminTools\Admin\Controller\TempSuperUsers'))
		{
			static::disableTempAdmins();
		}
	}

	/**
	 * Create the administrator menu for Akeeba Backup
	 */
	public static function adminMenu()
	{
		if (is_multisite())
		{
			return;
		}

		self::$menu_page = add_menu_page(
			'Admin Tools',
			'Admin Tools',
			'manage_options',
			self::$absoluteFileName,
			['AdminToolsWP', 'boot'],
			plugins_url('app/media/images/akeeba-admintools-wp-small-16.png', self::$absoluteFileName)
		);

		add_action('load-' . self::$menu_page, ['AdminToolsWP', 'add_options']);
	}

	/**
	 * Create the blog network administrator menu for Admin Tools
	 */
	public static function networkAdminMenu()
	{
		if (!is_multisite())
		{
			return;
		}

		self::$menu_page = add_menu_page(
			'Admin Tools',
			'Admin Tools',
			'manage_options',
			self::$absoluteFileName,
			['AdminToolsWP', 'boot'],
			plugins_url('app/media/images/akeeba-admintools-wp-small-16.png', self::$absoluteFileName)
		);

		add_action('load-' . self::$menu_page, ['AdminToolsWP', 'add_options']);
	}

	/**
	 * Boots the Akeeba Backup application
	 */
	public static function boot()
	{
		if (!defined('AKEEBA_COMMON_WRONGPHP'))
		{
			define('AKEEBA_COMMON_WRONGPHP', 1);
		}

		$minPHPVersion         = '7.4.0';
		$recommendedPHPVersion = '8.1';
		$softwareName          = 'Admin Tools for WordPress';

		if (!require_once(dirname(self::$absoluteFileName) . '/helpers/wrongphp.php'))
		{
			return;
		}

		// HHVM made sense in 2013, now PHP 7 is a way better solution than an hybrid PHP interpreter
		if (defined('HHVM_VERSION'))
		{
			include_once dirname(self::$absoluteFileName) . '/helpers/hhvm.php';

			return;
		}

		$network = is_multisite() ? 'network/' : '';

		if (!defined('ADMINTOOLSWP_URL'))
		{
			$bootstrapUrl = admin_url() . $network . 'admin.php?page=' . self::$dirName . '/' . self::$fileName;
			define('ADMINTOOLSWP_URL', $bootstrapUrl);
		}

		@ob_start();

		if (version_compare(PHP_VERSION, '7.0.0', 'ge'))
		{
			try
			{
				include_once dirname(self::$absoluteFileName) . '/helpers/bootstrap.php';

				Akeeba\AdminTools\Admin\Dispatcher\Dispatcher::route();
			}
			catch (Throwable $e)
			{
				@ob_end_clean();

				require_once __DIR__ . '/../app/view/error.php';
			}
		}
		else
		{
			include_once dirname(self::$absoluteFileName) . '/helpers/bootstrap.php';

			Akeeba\AdminTools\Admin\Dispatcher\Dispatcher::route();
		}

		@ob_end_flush();
	}

	/**
	 * Adds the Screen option to the page
	 */
	public static function add_options()
	{
		$screen = get_current_screen();

		// get out of here if we are not on our settings page
		if (!is_object($screen) || $screen->id != self::$menu_page)
		{
			return;
		}

		$args = [
			'default' => 20,
			'option'  => 'admintoolswp_per_page',
		];

		add_screen_option('per_page', $args);
	}

	/**
	 * Tells Wordpress that he should save our options
	 *
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function set_option($status, $option, $value)
	{
		$allowed = ['admintoolswp_per_page'];

		if (in_array($option, $allowed))
		{
			return $value;
		}

		return $status;
	}

	/**
	 * Starts output buffer in the admin area. Usually this is not required, since the plugin will start the buffering
	 * for us. However the plugin could be manually disabled, so I need a fallback to be 100% sure to have output
	 * buffering on
	 * (required for redirects using PHP headers)
	 */
	public static function startAdminBuffer()
	{
		$page = self::$dirName . '/' . self::$fileName;

		// Is this an Admin Tools page?
		if (isset($_REQUEST['page']) && ($_REQUEST['page'] == $page) && !defined('ADMNITOOLSWP_OBFLAG'))
		{
			define('ADMNITOOLSWP_OBFLAG', 1);
			@ob_start(['AdminToolsWP', 'clearAdminBuffer']);
		}
	}

	/**
	 * Callback function for "startAdminBuffer" it ensures that we're outputting the whole buffer in the admin area
	 *
	 * @param   string  $contents
	 *
	 * @return    string
	 */
	public static function clearAdminBuffer($contents)
	{
		return $contents;
	}

	/**
	 * Registers admin dashboard widgets.
	 *
	 * @return  void
	 * @since   1.6.0
	 */
	public static function registerDashboardWidgets()
	{
		if (!defined('ADMINTOOLSWP_PRO') || !ADMINTOOLSWP_PRO)
		{
			return;
		}

		if (is_multisite())
		{
			return;
		}

		wp_add_dashboard_widget(
			'atwp_adminwidget_graphs',
			Language::_('COM_ADMINTOOLS_ADMINWIDGET_GRAPHS_TITLE'),
			[\Akeeba\AdminTools\Admin\Widget\Graphs::class, 'display'],
			null,
			null,
			'normal',
			'low'
		);
	}

	/**
	 * Implements automatic blocking of temporary Super Users after they are expired
	 */
	protected static function disableTempAdmins()
	{
		try
		{
			// Find temporary Super Users who are expired
			$db      = Wordpress::getDb();
			$now     = new Date();
			$query   = $db->getQuery(true)
				->select(
					[
						$db->qn('user_id'),
					]
				)->from($db->qn('#__admintools_tempsuperusers'))
				->where($db->qn('expiration') . ' <= ' . $db->q($now->toSql()));
			$userIDs = $db->setQuery($query)->loadColumn(0);
		}
		catch (Exception $e)
		{
			// Database error. Bail out.
			return;
		}

		// No expired Super Users? Bail out.
		if (empty($userIDs))
		{
			return;
		}

		// Block the users, by removing all roles associated
		foreach ($userIDs as $userID)
		{
			wp_update_user(
				[
					'ID'   => $userID,
					'role' => '',
				]
			);
		}

		$userIDListForDatabase = implode(', ', array_map([$db, 'q'], $userIDs));

		// Remove the users from the #__admintools_tempsupers table as well
		$query = $db->getQuery(true)
			->delete($db->qn('#__admintools_tempsuperusers'))
			->where($db->qn('user_id') . ' IN (' . $userIDListForDatabase . ')');

		$db->setQuery($query)->execute();
	}
}
