<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

use Akeeba\AdminTools\Library\Database\Driver;
use Akeeba\AdminTools\Library\Uri\Uri;

defined('ADMINTOOLSINC') or die;

/**
 * This class contains all helper methods to interact with Wordpress code base
 *
 * @package Akeeba\AdminTools\Admin\Helper
 */
abstract class Wordpress
{
	/**
	 * List of media files (CSS or JS) that should be added to the document. In this way we can choose if we should
	 * add them using WP API (normal page) or manually add them (Component view)
	 *
	 * @var array
	 */
	private static $mediaStack = [];

	/** @var array List of admin entry points */
	private static $admin_pages = ['wp-admin'];

	/**
	 * Fetches the database connection from the global Wordpress object or creates a new one using wp-config settings
	 *
	 * @return \Akeeba\AdminTools\Library\Database\Driver|null
	 */
	public static function getDb()
	{
		global $wpdb;

		// Do we have a reference to WP database (ie we are WITHIN WP)? Great, let's share the same connection
		if (isset($wpdb) && isset($wpdb->dbh))
		{
			$options['connection'] = $wpdb->dbh;
			$options['driver']     = 'mysqli';
			$options['prefix']     = $wpdb->prefix;

			// On multisite install we have to use the "root" prefix, so we have to fetch it on our own, WP would
			// return something like wp_2 etc etc
			if (function_exists('is_multisite') && is_multisite())
			{
				$root = Wordpress::getSiteRoot();

				if ($root)
				{
					$config            = self::parseWpconfig($root . '/wp-config.php');
					$options['prefix'] = $config['prefix'];
				}
			}

			if (!is_object($wpdb->dbh) || !($wpdb->dbh instanceof \mysqli))
			{
				$options['driver'] = (function_exists('mysql_connect') ? 'mysql' : 'mysqli');
			}

			$db = Driver::getInstance($options);

			return $db;
		}

		// No WP database? It means we're in auto-prepend mode, so we have to craft our own connection
		$root = Wordpress::getSiteRoot();

		// If we don't know the site root let's stop here
		if (!$root)
		{
			return null;
		}

		$config = self::parseWpconfig($root . '/wp-config.php');

		$options['driver']   = self::get_db_driver($config['force_mysql']);
		$options['database'] = $config['database'];
		$options['host']     = $config['host'];
		$options['user']     = $config['user'];
		$options['password'] = $config['password'];
		$options['prefix']   = $config['prefix'];

		$db = Driver::getInstance($options);

		return $db;
	}

	/**
	 * Adds a Javascript file to the stack of files that should be loaded
	 *
	 * @param   string  $file
	 * @param   array   $deps
	 */
	public static function enqueueScript($file, $deps = [])
	{
		$id = basename($file);

		$src = $file;

		if ((substr($src, 0 ,7) !== 'http://') && (substr($src, 0 ,8) !== 'https://')) {
			$src = ADMINTOOLSWP_MEDIAURL . '/app/media/js/' . $file;
		}

		static::$mediaStack['js'][$id] = [
			'src'  => $src,
			'deps' => $deps,
		];
	}

	/**
	 * Adds a CSS file to the stack of files that should be loaded
	 *
	 * @param   string  $file
	 * @param   array   $deps
	 */
	public static function enqueueStyle($file, $deps = [])
	{
		$id = basename($file);

		static::$mediaStack['css'][$id] = [
			'src'  => ADMINTOOLSWP_MEDIAURL . '/app/media/' . $file,
			'deps' => $deps,
		];
	}

	/**
	 * Adds media files to the document using WordPress API
	 */
	public static function addMediaToWordPress()
	{
		$version = md5(ADMINTOOLSWP_VERSION . ADMINTOOLSWP_DATE . filemtime(__DIR__ . '/../../version.php'));

		foreach (static::$mediaStack as $type => $items)
		{
			if ($type == 'css')
			{
				foreach ($items as $id => $item)
				{
					wp_enqueue_style($id, $item['src'], $item['deps'], $version, 'screen');
				}
			}
			elseif ($type == 'js')
			{
				foreach ($items as $id => $item)
				{
					wp_enqueue_script($id, $item['src'], $item['deps']);
				}
			}
		}
	}

	/**
	 * Creates raw HTML code ready to be pushed inside the HEAD element
	 */
	public static function getMediaForPage()
	{
		$html = [];

		foreach (static::$mediaStack as $type => $items)
		{
			if ($type == 'css')
			{
				foreach ($items as $id => $item)
				{
					// We do not handle dependencies since we do not use them for CSS files
					$html[$id] = '<link href="' . $item['src'] . '" rel="stylesheet" type="text/css">';
				}
			}
			elseif ($type == 'js')
			{
				foreach ($items as $id => $item)
				{
					// We do not handle dependencies since it's a a very complicated job and usually
					// "component" pages are pretty simple
					$html[$id] = '<script type="text/javascript" src="' . $item['src'] . '"></script>';
				}
			}
		}

		$html = implode("\n", $html);

		return $html;
	}

	/**
	 * Queries WP options and tries to get the timezone as a string.
	 * Users could set a proper timezone string (Europe/Rome) or use a manual
	 * offset (UTC +2)
	 * More info:
	 *
	 * http://wordpress.stackexchange.com/questions/8400/how-to-get-wordpress-time-zone-setting
	 * https://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
	 *
	 * @return string
	 */
	public static function get_timezone_string()
	{
		// If site timezone string exists, return it
		$timezone = self::get_option('timezone_string');

		if ($timezone)
		{
			return $timezone;
		}

		// Get UTC offset, if it isn't set then return UTC
		$utc_offset = self::get_option('gmt_offset', '0');

		if ($utc_offset === '0')
		{
			return 'UTC';
		}

		// Adjust UTC offset from hours to seconds
		$utc_offset *= 3600;

		// Attempt to guess the timezone string from the UTC offset
		$timezone = timezone_name_from_abbr('', $utc_offset, 0);

		if ($timezone)
		{
			return $timezone;
		}

		// Last try, guess timezone string manually
		$is_dst = date('I');

		foreach (timezone_abbreviations_list() as $abbr)
		{
			foreach ($abbr as $city)
			{
				if (($city['dst'] == $is_dst) && ($city['offset'] == $utc_offset))
				{
					return $city['timezone_id'];
				}
			}
		}

		// Fallback to UTC
		return 'UTC';
	}

	/**
	 * Retrieves a Wordpress option. Useful when we are in auto-prepend mode and we can't use core functions
	 *
	 * @param   string  $option   Name of the option to retrieve
	 * @param   mixed   $default  Default value in case the option is missing
	 *
	 * @return mixed
	 */
	public static function get_option($option, $default = null)
	{
		static $cache;

		if (!isset($cache[$option]))
		{
			$db    = self::getDb();
			$query = $db->getQuery(true)
				->select($db->qn('option_value'))
				->from($db->qn('#__options'))
				->where($db->qn('option_name') . ' = ' . $db->q($option));

			$row = $db->setQuery($query)->loadObject();

			// If the option is missing, let's return the default. DO NOT CACHE THE RESULT, since different
			// usages could have different default values. This should be a very edge case, so we can live with one extra query
			if (!is_object($row))
			{
				return $default;
			}

			$cache[$option] = $row->option_value;
		}

		return $cache[$option];
	}

	/**
	 * Retrieves site meta
	 *
	 * @param   string  $key      Name of the option to retrieve
	 * @param   mixed   $default  Default value in case the option is missing
	 * @param   mixed   $site_id  Site ID we're going to
	 *
	 * @return mixed
	 */
	public static function get_site_meta($key, $default = null, $site_id = null)
	{
		static $cache;

		// By default set it to 1. If we have access to WordPress functions, let's ask him to fetch the correct ID
		if (!$site_id)
		{
			$site_id = 1;
		}

		if (function_exists('get_current_network_id'))
		{
			$site_id = get_current_network_id();
		}

		$cache_key = $site_id . '.' . $key;

		if (!isset($cache[$cache_key]))
		{
			$db    = self::getDb();
			$query = $db->getQuery(true)
				->select($db->qn('meta_value'))
				->from($db->qn('#__sitemeta'))
				->where($db->qn('site_id') . ' = ' . $db->q($site_id))
				->where($db->qn('meta_key') . ' = ' . $db->q($key));

			try
			{
				$row = $db->setQuery($query)->loadObject();
			}
			catch (\Exception $e)
			{
				// The above query could fail if we're not in multisite intallation, however we can't use is_multisite
				// since we could be running in auto-prepend mode
				$cache[$cache_key] = $default;

				return $default;
			}

			// If the option is missing, let's return the default. DO NOT CACHE THE RESULT, since different
			// usages could have different default values. This should be a very edge case, so we can live with one extra query
			if (!is_object($row))
			{
				return $default;
			}

			$cache[$cache_key] = $row->meta_value;
		}

		return $cache[$cache_key];
	}

	/**
	 * Fetches the page limit for the current user, retrieving it from WP user options
	 *
	 * @return int
	 */
	public static function get_page_limit()
	{
		// If any WP function is missing, return the default value
		if (!function_exists('get_current_user_id') || !function_exists('get_current_screen') || !function_exists('get_user_meta'))
		{
			return 20;
		}

		$user          = get_current_user_id();
		$screen        = get_current_screen();
		$screen_option = $screen->get_option('per_page', 'option');

		// Sanity check, it seems that sometimes the option is not set
		if (!$screen_option)
		{
			return 20;
		}

		$limit = get_user_meta($user, $screen_option, true);

		// If the user never set a value, let's use the default one
		if ($limit === "")
		{
			$limit = 20;
		}

		return $limit;
	}

	/**
	 * Checks the current URL and guess if we're using the admin area of the site
	 *
	 * @param   string  $uri
	 *
	 * @return bool
	 */
	public static function is_admin($uri = 'SERVER')
	{
		static $cache = [];

		$uri = Uri::getInstance($uri);
		$url = $uri->toString(['host', 'path']);

		$hash = md5($url);

		if (!isset($cache[$hash]))
		{
			$site_url = self::get_option('siteurl');
			$home_url = self::get_option('home');

			// Remove the schema
			$site_url = Uri::getInstance($site_url)->toString(['host', 'path']);
			$home_url = Uri::getInstance($home_url)->toString(['host', 'path']);

			// Remove the site URL so I can get the path
			$path = str_replace($site_url, '', $url);
			$path = str_replace($home_url, '', $path);
			$path = trim($path, '/\\');

			// Pop the first element of the array, so we can get the folder being accessed
			$parts  = explode('/', $path);
			$folder = array_shift($parts);

			$cache[$hash] = in_array($folder, self::$admin_pages);
		}

		return $cache[$hash];
	}

	/**
	 * Checks the current URL and guess if we're doing an AJAX request or not
	 *
	 * @param   string  $uri
	 *
	 * @return bool
	 */
	public static function is_ajax($uri = 'SERVER')
	{
		// If we have the constant and it's true, it's an AJAX request for sure
		if (defined('DOING_AJAX') && DOING_AJAX)
		{
			return true;
		}

		// Nothing? Let's check the entry point, we could be in auto-prepend mode, so some constants are not defined yet
		$uri = Uri::getInstance($uri);
		$url = $uri->toString(['host', 'path']);

		if (stripos($url, 'admin-ajax.php') !== false)
		{
			return true;
		}

		return false;
	}

	/**
	 * Function to be used with apply_filters to set outgoing emails in html.
	 * We have to define a function so we can remove such filter and avoid issues
	 *
	 * @return string
	 */
	public static function set_mail_html()
	{
		return 'text/html';
	}

	/**
	 * Function to be used with apply_filters to set outgoing emails priority.
	 * We have to define a function so we can remove such filter and avoid issues
	 *
	 * @param   \PHPMailer  $mailer
	 *
	 * @return \PHPMailer
	 */
	public static function set_mail_priority($mailer)
	{
		$mailer->Priority = 3;

		return $mailer;
	}

	/**
	 * Parses wp-config.php file and extracts all constants from the file, trying to use different techniques.
	 *
	 * @param   string  $config_file  Path to the file containing site configuration
	 *
	 * @return array
	 */
	public static function parseWpconfig($config_file)
	{
		if (!file_exists($config_file))
		{
			return [];
		}

		$contents = file_get_contents($config_file);

		// Do we have the tokenizer extension loaded? That's great!
		if (function_exists('token_get_all'))
		{
			$config = self::parseWpconfigToken($contents);
		}
		// Otherwise fallback to raw line inspections, that's not 100% reliable
		else
		{
			$config = self::parseWpconfigRaw($contents);
		}

		return $config;
	}

	/**
	 * Detects the database driver that should be used. FYI WordPress has no support for PDO, so it's down to
	 * mysql or mysqli
	 *
	 * @param   bool  $force_mysql  Should we force mysql even if mysqli is available?
	 *
	 * @return  string  Database driver that should be used
	 */
	public static function get_db_driver($force_mysql = false)
	{
		// Apply the same logic of WP to guess if we really have to use mysql OR mysqli
		$driver = 'mysql';

		if (function_exists('mysqli_connect'))
		{
			if ($force_mysql)
			{
				$driver = 'mysql';
			}
			elseif (version_compare(phpversion(), '5.5', '>=') || !function_exists('mysql_connect'))
			{
				$driver = 'mysqli';
			}
		}

		return $driver;
	}

	/**
	 * Sends out an email: if the global wp_mail function is not available, it will be added to the
	 * queue and fired by WP Cron system
	 *
	 * @param   array   $recipients  Array containing recipients email addresses
	 * @param   string  $subject     Email subject
	 * @param   string  $body        Email body
	 * @param   bool    $enqueue     If the global mail function does not exist, should I enqueue the email?
	 * @param   bool    $html        Should I send the email in HTML or raw text?
	 *
	 * @return  bool    Did I succeed on sending (or queuing) the email?
	 */
	public static function sendEmail(array $recipients, $subject, $body, $enqueue = true, $html = true)
	{
		// If we're running tests, we always want to enqueue the email instead of sending them
		if (file_exists(__DIR__ . '/../assets/email.test'))
		{
			$enqueue = true;
		}

		// No wp_mail and I can't enqueue? Let's stop here, since I can't do anything
		if (!function_exists('wp_mail') && !$enqueue)
		{
			return false;
		}

		// We want to send the email immediately
		if (function_exists('wp_mail') && !$enqueue)
		{
			if ($html)
			{
				add_filter('wp_mail_content_type', '\Akeeba\AdminTools\Admin\Helper\Wordpress::set_mail_html');
			}

			add_filter('phpmailer_init', '\Akeeba\AdminTools\Admin\Helper\Wordpress::set_mail_priority');

			$success = true;

			foreach ($recipients as $recipient)
			{
				if (empty($recipient))
				{
					continue;
				}

				$success = wp_mail($recipient, $subject, $body);
			}

			if ($html)
			{
				remove_filter('wp_mail_content_type', '\Akeeba\AdminTools\Admin\Helper\Wordpress::set_mail_html');
			}

			remove_filter('phpmailer_init', '\Akeeba\AdminTools\Admin\Helper\Wordpress::set_mail_priority');

			return $success;
		}

		// No available mailer? Let's add to the queue
		$db = self::getDb();

		$email_data = (object) [
			'recipients' => $recipients,
			'subject'    => $subject,
			'body'       => $body,
			'html'       => $html,
		];

		$email_data = json_encode($email_data);

		$data = (object) [
			'at_key'   => 'mail_' . (sha1(random_bytes(12) . microtime())),
			'at_value' => $email_data,
		];

		$db->insertObject('#__admintools_storage', $data);

		return true;
	}

	/**
	 * Returns the absolute path to the root site.
	 *
	 * @return bool|string
	 */
	public static function getSiteRoot()
	{
		// If it's already defined, we're lucky
		if (defined('ABSPATH'))
		{
			return ABSPATH;
		}

		// Used for local development
		if (getenv('ABSPATH'))
		{
			return getenv('ABSPATH');
		}

		// The default is "sorry, I found no root"
		$root = false;

		$scriptBasePath = is_null($_SERVER['SCRIPT_FILENAME']) ? '' : dirname($_SERVER['SCRIPT_FILENAME']);
		$possibleRoots  = [
			ADMINTOOLSWP_PATH,
			getcwd(),
			$scriptBasePath,
			($scriptBasePath == '') ? '' : (DIRECTORY_SEPARATOR . trim($scriptBasePath, '/\\')),
		];

		foreach ($possibleRoots as $startingPoint)
		{
			for ($i = 0; $i <= 10; $i++)
			{
				$relPath = $startingPoint . str_repeat('/..', $i);

				// Did I just hit an open_basedir restriction...?
				if (!@is_dir($relPath))
				{
					break 1;
				}

				if (!@file_exists($relPath . '/wp-config.php'))
				{
					continue 1;
				}

				return realpath($relPath);
			}
		}

		foreach ($possibleRoots as $startingPoint)
		{
			$parts = explode(DIRECTORY_SEPARATOR, $startingPoint);

			while (count($parts))
			{
				array_pop($parts);

				if (empty($parts))
				{
					break 1;
				}

				$relPath = implode(DIRECTORY_SEPARATOR, $parts);

				// Did I just hit an open_basedir restriction...?
				if (!@is_dir($relPath))
				{
					break 1;
				}

				if (!@file_exists($relPath . '/wp-config.php'))
				{
					continue 1;
				}

				return realpath($relPath);
			}
		}

		return $root;
	}

	/**
	 * Detect if a plugin is actually active by running raw queries on the database. We have to rely on our functions
	 * since when we're in auto-prepend mode we have no access to WordPress API
	 *
	 * @param $plugin
	 *
	 * @return bool
	 */
	public static function isPluginActive($plugin)
	{
		$active_plugins = static::get_option('active_plugins', '');
		$active_plugins = unserialize($active_plugins);

		if (is_array($active_plugins) && in_array($plugin, $active_plugins))
		{
			return true;
		}

		$sitewide_plugins = static::get_site_meta('active_sitewide_plugins', '');
		$sitewide_plugins = unserialize($sitewide_plugins);

		if (is_array($sitewide_plugins) && isset($sitewide_plugins[$plugin]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the WordPress administration level of the user. In parentheses the equivalent Joomla core role.
	 *
	 * 3 : Super Admin. Has full administrator privileges. On single sites the user has adequate privileges to delete
	 *     other users. On multisite installations it's the network admin role. (Super User)
	 *
	 * 2 : Administrator. Has the privilege to activate plugins. On multisites this is the blog admin role. (Admin)
	 *
	 * 1 : Editor. The user has privileges to edit other users' blog posts. Does not have enough privileges to make
	 *     more critical operations but can still be abused to deface a site (Publisher)
	 *
	 * 0 : Any other user, non-privileged. May or may not have the ability to write / publish own posts and/or comments
	 *     (Registered, ...)
	 *
	 * @param   int|object  $user  WordPress user id or user object
	 *
	 * @return  int  The administration capability level, see above
	 *
	 * @since   1.0.0.
	 */
	public static function getUserAdminLevel($user)
	{
		if (!function_exists('get_userdata'))
		{
			return 0;
		}

		if (is_int($user))
		{
			$userData = get_userdata($user);
			$user_id  = $userData->ID;
			$allcaps  = $userData->allcaps;
		}
		elseif (isset($user->allcaps) && isset($user->ID))
		{
			$user_id = $user->ID;
			$allcaps = $user->allcaps;
		}
		else
		{
			return 0;
		}

		$isSuperAdmin = is_super_admin($user_id);
		$isEditor     = false;
		$isAdmin      = false;

		if (is_array($allcaps) && isset($allcaps['edit_others_posts']))
		{
			$isEditor = $allcaps['edit_others_posts'];
		}

		if (is_array($allcaps) && isset($allcaps['activate_plugins']))
		{
			$isAdmin = $allcaps['activate_plugins'];
		}

		if ($isSuperAdmin)
		{
			return 3;
		}

		if ($isAdmin)
		{
			return 2;
		}

		if ($isEditor)
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Parses configuration file using tokenizer functions. More reliable, but we must have the tokenizer extension
	 * enabled. That's a standard library, but some host keep disabling it...
	 *
	 * @param $fileContents
	 *
	 * @return array|mixed
	 */
	protected static function parseWpconfigToken($fileContents)
	{
		$tokens = token_get_all($fileContents);

		$commentTokens = [T_COMMENT];

		if (defined('T_DOC_COMMENT'))
		{
			$commentTokens[] = T_DOC_COMMENT;
		}

		if (defined('T_ML_COMMENT'))
		{
			$commentTokens[] = T_ML_COMMENT;
		}

		$newStr = '';

		foreach ($tokens as $token)
		{
			if (is_array($token))
			{
				if (in_array($token[0], $commentTokens))
				{
					/**
					 * If the comment ended in a newline we need to output the newline. Otherwise we will have
					 * run-together lines which won't be parsed correctly by parseWithoutTokenizer.
					 */
					if (substr($token[1], -1) == "\n")
					{
						$newStr .= "\n";
					}

					continue;
				}

				$token = $token[1];
			}

			$newStr .= $token;
		}

		return self::parseWpconfigRaw($newStr);
	}

	/**
	 * Parses the configuration file using raw line inspection. We have to use that in case tokenizer functions are not
	 * enabled. It works fine except in case we have multi line comments with valid code inside.
	 *
	 * @param $fileContents
	 *
	 * @return array|mixed
	 */
	protected static function parseWpconfigRaw($fileContents)
	{
		$config['force_mysql'] = false;

		//Ok, now let's start analyzing
		// PLEASE NOTE: We can't skip or remove multi-line comments, since the starting/closing tag could clash with
		// a legit database password. The Right Way to handle this would be to parse PHP tokens, but the tokenize extension
		// is not always available, sigh...
		$lines = explode("\n", $fileContents);

		foreach ($lines as $line)
		{
			$line = trim($line);

			// If this line starting with a comment? If so let's ignore it
			if ((strpos($line, '#') === 0) || (strpos($line, '//') === 0))
			{
				continue;
			}

			// Search for defines
			if (strpos($line, 'define') === 0)
			{
				list ($key, $value) = self::parseDefine($line);

				switch (strtoupper($key))
				{
					case 'DB_NAME':
						$config['database'] = $value;
						break;

					case 'DB_USER':
						$config['user'] = $value;
						break;

					case 'DB_PASSWORD':
						$config['password'] = $value;
						break;

					case 'DB_HOST':
						$config['host'] = $value;
						break;

					// Special constant used to force loading mysql instead of mysqli driver
					case 'WP_USE_EXT_MYSQL':
						$config['force_mysql'] = $value;
						break;
				}
			}
			// Table prefix
			elseif (strpos($line, '$table_prefix') === 0)
			{
				$parts            = explode('=', $line, 2);
				$prefixData       = trim($parts[1]);
				$config['prefix'] = self::parseStringDefinition($prefixData);
			}
		}

		return $config;
	}

	/**
	 * Parse a PHP file line with a define statement and return the constant name and its value
	 *
	 * @param   string  $line  The line to parse
	 *
	 * @return  array  array($key, $value)
	 */
	protected static function parseDefine($line)
	{
		$pattern    = '#define\s*\(\s*(["\'][A-Z_]*["\'])\s*,\s*(["\'].*["\'])\s*\)\s*;#u';
		$numMatches = preg_match($pattern, $line, $matches);

		if ($numMatches < 1)
		{
			return ['', ''];
		}

		$key   = trim($matches[1], '"\'');
		$value = $matches[2];

		$value = self::parseStringDefinition($value);

		if (is_null($value))
		{
			return ['', ''];
		}

		return [$key, $value];
	}

	/**
	 * Parses a string definition, surrounded by single or double quotes, removing any comments which may be left tucked
	 * to its end, reducing escaped characters to their unescaped equivalent and returning the clean string.
	 *
	 * @param   string  $value
	 *
	 * @return  null|string  Null if we can't parse $value as a string.
	 */
	protected static function parseStringDefinition($value)
	{
		// At this point the value may be in the form 'foobar');#comment'gargh" if the original line was something like
		// define('DB_NAME', 'foobar');#comment'gargh");

		$quote = $value[0];

		// The string ends in a different quote character. Backtrack to the matching quote.
		if (substr($value, -1) != $quote)
		{
			$lastQuote = strrpos($value, $quote);

			// WTF?!
			if ($lastQuote <= 1)
			{
				return null;
			}

			$value = substr($value, 0, $lastQuote + 1);
		}

		// At this point the value may be cleared but still in the form 'foobar');#comment'
		// We need to parse the string like PHP would. First, let's trim the quotes
		$value = trim($value, $quote);

		$pos = 0;

		while ($pos !== false)
		{
			$pos = strpos($value, $quote, $pos);

			if ($pos === false)
			{
				break;
			}

			if (substr($value, $pos - 1, 1) == '\\')
			{
				$pos++;

				continue;
			}

			$value = substr($value, 0, $pos);
		}

		// Finally, reduce the escaped characters.

		if ($quote == "'")
		{
			// Single quoted strings only escape single quotes and backspaces
			$value = str_replace(["\\'", "\\\\",], ["'", "\\"], $value);
		}
		else
		{
			// Double quoted strings just need stripslashes.
			$value = stripslashes($value);
		}

		return $value;
	}
}
