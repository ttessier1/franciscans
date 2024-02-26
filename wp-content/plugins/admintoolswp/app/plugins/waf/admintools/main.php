<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Database\Driver;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Registry\Registry;
use Akeeba\AdminTools\Library\Uri\Uri;
use Akeeba\AdminTools\Library\Utils\Ip;

defined('ADMINTOOLSINC') or die;

// This dummy class is here to allow the class autoloader to load the main plugin file
class AtsystemAdmintoolsMain
{

}

/**
 * This class acts as a proxy to the feature classes
 *
 */
class plgSystemAdmintools
{
	/** @var   Storage   Component parameters */
	protected $componentParams = null;

	/** @var   array  Maps plugin hooks (onSomethingSomething) to feature objects */
	protected $featuresPerHook = [];

	/** @var   Input  Global input */
	protected $input = null;

	/** @var   AtsystemUtilExceptionshandler  The security exceptions handler */
	protected $exceptionsHandler = null;

	/** @var   array  The applicable WAF Exceptions which prevent filtering from taking place */
	public $exceptions = [];

	/** @var   bool   Should I skip filtering (because of whitelisted IPs, WAF Exceptions etc) */
	public $skipFiltering = false;

	/** @var Driver|null  */
	public $db = null;

	/** @var Registry  */
	public $params;

	/**
	 * Initialises the System - Admin Tools plugin
	 */
	public function __construct($input)
	{
		$this->params = Params::getInstance();

		if(is_null($this->db))
		{
			$this->db = Wordpress::getDb();
		}

		// Something went really bad while trying to fetch the database object. Let's stop the execution or we will break the site
		if (!$this->db)
		{
			return;
		}

		// Store a reference to the global input object
		$this->input = $input;

		// Load the component parameters
		$this->loadComponentParameters();

		// Work around IP issues with transparent proxies etc
		$this->workaroundIP();

		// Preload the security exceptions handler object
		$this->loadExceptionsHandler();

		// Load the WAF Exceptions
		$this->loadWAFExceptions();

		// Load and register the plugin features
		$this->loadFeatures();
	}

	/**
	 * Registers Admin Tools features using Wordpress hooks
	 */
	public function registerFeatures()
	{
		add_action('muplugins_loaded', [$this, 'onWordPressLoad'], 1);

		// First of all let's enable output buffering on whole WP frontend
		add_action('init', [$this, 'startBuffer'], 1);
		add_action('init', [$this, 'onWordPressInit'], 2);

		add_filter('authenticate'               , [$this, 'onUserAuthenticate'], 1, 3);
		add_action('wp_login'                   , [$this, 'onUserLogin'], 10, 2);
		add_action('wp_login_failed'            , [$this, 'onUserLoginFailure'], 1);
		add_action('clear_auth_cookie'          , [$this, 'onUserLogout'], 1);
		add_action('auth_cookie_expired'        , [$this, 'onUserCookieExpired'], 99, 1);
		add_filter('registration_errors'        , [$this, 'onUserBeforeRegister'], 10, 3);
		add_action('user_profile_update_errors' , [$this, 'onUserBeforeSave'], 10, 3);
		add_action('user_register'              , [$this, 'onUserAfterSave'], 99, 1);

		add_filter('auth_cookie_expiration' , [$this, 'onSessionStart'], 99, 3);
		add_filter('login_errors'           , [$this, 'onLoginErrorMessage'], 1);
		add_action('admin_notices'          , [$this, 'onAdminNotices'], 1);
		add_action('network_admin_notices'  , [$this, 'onNetworkAdminNotices'], 1);

		// Sometimes we have to simply add/remove actions or filters.
		// Our features will hook on this event and do their own job
		$this->runFeature('onCustomHooks', []);
	}

	/**
	 * Runs "system" events, immediately after WordPress was loaded or, if we're in auto-prepend mode,
	 * the page was requested
	 */
	public function onSystem()
	{
		return $this->runFeature('onSystem', []);
	}

	/**
	 * Fires as soon as WordPress finished loading (hooks to the muplugins_loaded action)
	 */
	public function onWordPressLoad()
	{
		$this->runFeature('onWordPressLoad', []);
	}

	/**
	 * Fires when WordPress has been fully init (some core functions are not ready before this point)
	 */
	public function onWordPressInit()
	{
		$this->runFeature('onWordPressInit', []);
	}

	/**
	 * Hook to change the contents of the page right before they are sent to the browser
	 *
	 * @param   string  $contents
	 *
	 * @return  string  The new contents of the page
	 */
	public function onBeforeRender($contents = '')
	{
		$new_contents = $this->runFeature('onBeforeRender', [$contents]);

		if ($new_contents)
		{
			return $new_contents;
		}

		// If no feature ran, let's return the original contents
		return $contents;
	}

	/**
	 * Hooks on the event when session cookies are set
	 *
	 * @param   int     $expiration     Expiration time (in seconds)
	 * @param   int     $user_id        User ID
	 * @param   bool    $remember       "Remember me" flag
	 *
	 * @return mixed
	 */
	public function onSessionStart($expiration = 0, $user_id = 0, $remember = false)
	{
		$result = $this->runFeature('onSessionStart', [$expiration, $user_id, $remember]);

		// This function MUST return something, otherwise cookies are not set. If no features
		// are enabled, let's return the same expiration we got
		if (!$result)
		{
			return $expiration;
		}

		return $result;
	}

	/**
	 * Runs when the user tries to authenticate inside the admin area of WordPress
	 *
	 * @param null|WP_Error|WP_User $user
	 * @param string                $username
	 * @param string                $password
	 *
	 * @return  WP_Error|WP_User
	 */
	public function onUserAuthenticate($user = null, $username = '', $password = '')
	{
		return $this->runFeature('onUserAuthenticate', [$user, $username, $password]);
	}

	/**
	 * Called when a user enters the wrong access details and WP is displaying the error message
	 *
	 * @param   string  $error  Original error message
	 *
	 * @return mixed
	 */
	public function onLoginErrorMessage($error = '')
	{
		$new_error = $this->runFeature('onLoginErrorMessage', [$error]);

		// If the feature is not enabled, let's return the original error message
		if (!$new_error)
		{
			return $error;
		}

		return $new_error;
	}

	/**
	 * Called when a user fails to log in

	 * @return mixed
	 */
	public function onUserLoginFailure()
	{
		return $this->runFeature('onUserLoginFailure', []);
	}

	/**
	 * Called when a user is logging out
	 *
	 * @return mixed
	 */
	public function onUserLogout()
	{
		return $this->runFeature('onUserLogout', []);
	}

	/**
	 * Fired when the cookie expires
	 *
	 * @param   array   $cookie_params
	 *
	 * @return void
	 */
	public function onUserCookieExpired($cookie_params = [])
	{
		$this->runFeature('onUserCookieExpired', [$cookie_params]);
	}

	/**
	 * @param   string      $user_login
	 * @param   \WP_User    $user
	 *
	 * @return mixed
	 */
	public function onUserLogin($user_login = '', $user = null)
	{
		return $this->runFeature('onUserLogin', [$user_login, $user]);
	}

	/**
	 * Fires before a user is saved (added or updated)
	 *
	 * @param   \WP_Error   $errors     Holds the full stack of WP errors
	 * @param   bool        $update     Is this a new user or updating an existing one?
	 * @param   \stdClass   $user       WordPress user class
	 */
	public function onUserBeforeSave(&$errors, $update, &$user)
	{
		$this->runFeature('onUserBeforeSave', [&$errors, $update, &$user]);
	}

	/**
	 * Fires as soon as the user as been saved
	 *
	 * @param   int $user
	 *
	 * @return mixed
	 */
	public function onUserAfterSave($user = 0)
	{
		return $this->runFeature('onUserAfterSave', [$user]);
	}

	/**
	 * @param   \WP_Error   $errors
	 * @param   string      $sanitized_user_login
	 * @param   string      $user_email
	 *
	 * @return  \WP_Error
	 */
	public function onUserBeforeRegister($errors, $sanitized_user_login, $user_email)
	{
		$new_errors = $this->runFeature('onUserBeforeRegister', [$errors, $sanitized_user_login, $user_email]);

		if ($new_errors)
		{
			return $new_errors;
		}

		// If the feature is disabled, I still have to return something to the caller
		return $errors;
	}

	/**
	 * Called to display notices in the admin area (on every page, not only inside Admin Tools)
	 */
	public function onAdminNotices()
	{
		// No need to pass anything, each feature should directly echo in the admin area
		$this->runFeature('onAdminNotices', []);
	}

	/**
	 * Called to display notices in the network admin area (on every page, not only inside Admin Tools)
	 */
	public function onNetworkAdminNotices()
	{
		// No need to pass anything, each feature should directly echo in the admin area
		$this->runFeature('onNetworkAdminNotices', []);
	}

	/**
	 * Loads the component parameters model into $this->componentParams
	 *
	 * @return  void
	 */
	protected function loadComponentParameters()
	{
		require_once ADMINTOOLSWP_PATH.'/app/helper/storage.php';

		$this->componentParams = Storage::getInstance();
	}

	/**
	 * Work around non-transparent proxy and reverse proxy IP issues
	 *
	 * @return  void
	 */
	protected function workaroundIP()
	{
		// IP workarounds are always disabled in the Core version
		if (!defined('ADMINTOOLS_PRO'))
		{
			@include_once ADMINTOOLSWP_PATH . '/version.php';
		}

		if (!ADMINTOOLSWP_PRO)
		{
			return;
		}

		$enableWorkarounds = $this->componentParams->getValue('ipworkarounds', -1);

		// Upgrade from older versions (default: enable IP workarounds)
		if ($enableWorkarounds == -1)
		{
			$enableWorkarounds = 1;
			$this->componentParams->setValue('ipworkarounds', 1, true);
		}

		if (!$enableWorkarounds)
		{
			return;
		}

		Ip::setAllowIpOverrides($enableWorkarounds);
		Ip::workaroundIPIssues();
	}

	/**
	 * Loads the security exception handler object, if present
	 *
	 * @return  void
	 */
	protected function loadExceptionsHandler()
	{
		if (class_exists('AtsystemUtilExceptionshandler'))
		{
			$this->exceptionsHandler = new AtsystemUtilExceptionshandler($this->params, $this->componentParams);
		}
	}

	/**
	 * Starts output buffering. Since the "init" hook is in common both in admin and site area, this function will be always
	 * invoked, as long as Admin Tools plugin is enabled.
	 */
	public function startBuffer()
	{
		if (!defined('ADMNITOOLSWP_OBFLAG'))
		{
			define('ADMNITOOLSWP_OBFLAG', 1);
			@ob_start([&$this, 'clearBuffer']);
		}
	}

	/**
	 * Clears and outputs the buffer in the site area. Here we will trigger some actions to allow our plugin to perform
	 * some last-minute changes to the contents
	 *
	 * @param 	string	$contents
	 *
	 * @return string
	 */
	public function clearBuffer($contents)
	{
		return $this->onBeforeRender($contents);
	}

	/**
	 * Loads the Admin Tools feature classes and register their hooks with this plugin
	 *
	 * @return  void
	 */
	protected function loadFeatures()
	{
		// Load all enabled features
		$di = new DirectoryIterator(__DIR__ . '/../feature');
		$features = [];

		/** @var DirectoryIterator $fileSpec */
		foreach ($di as $fileSpec)
		{
			if ($fileSpec->isDir())
			{
				continue;
			}

			// Get the filename minus the .php extension
			$fileName = $fileSpec->getFilename();
			$fileName = substr($fileName, 0, -4);

			if (in_array($fileName, ['interface', 'abstract']))
			{
				continue;
			}

			$className = 'AtsystemFeature' . ucfirst($fileName);

			if (!class_exists($className, true))
			{
				continue;
			}

			/** @var AtsystemFeatureAbstract $o */
			$o = new $className($this->db, $this->params, $this->componentParams, $this->input, $this->exceptionsHandler, $this->exceptions, $this->skipFiltering);

			if (!$o->isEnabled())
			{
				continue;
			}

			$features[] = [$o->getLoadOrder(), $o];
		}

		// Make sure we have some enabled features
		if (empty($features))
		{
			return;
		}

		// Sort the features by load order
		uasort($features, function ($a, $b)
		{
			if ($a[0] == $b[0])
			{
				return 0;
			}

			return ($a[0] < $b[0]) ? -1 : 1;
		});

		foreach ($features as $featureDef)
		{
			$feature = $featureDef[1];

			$className = get_class($feature);

			$methods = get_class_methods($className);

			foreach ($methods as $method)
			{
				if (substr($method, 0, 2) != 'on')
				{
					continue;
				}

				if (!isset($this->featuresPerHook[$method]))
				{
					$this->featuresPerHook[$method] = [];
				}

				$this->featuresPerHook[$method][] = $feature;
			}
		}
	}

	/**
	 * Load the applicable WAF exceptions for this request
	 */
	protected function loadWAFExceptions()
	{
		$db = $this->db;

		$query = $db->getQuery(true)
					->select('*')
					->from('#__admintools_wafexceptions')
					->where($db->qn('published').' = '.$db->q(1));

		try
		{
			$rules = $db->setQuery($query)->loadObjectList();
		}
		catch (\Exception $e)
		{
			// Do not die if anything wrong happens
			return;
		}

		// No rules, no need to continue
		if (!$rules)
		{
			return;
		}

		foreach ($rules as $rule)
		{
			if ($this->wafExceptionMatches($rule))
			{
				$this->skipFiltering = true;

				break;
			}
		}
	}

	/**
	 * Checks if a WAF Exception rule matches with the current request
	 *
	 * @param $rule
	 *
	 * @return bool
	 */
	private function wafExceptionMatches($rule)
	{
		$url 	 = Uri::getInstance();
		$current = $url->toString(['host', 'path', 'query']);

		// Get the site URL, so we can make it relative
		$siteurl = Wordpress::get_option('siteurl');
		$siteurl = str_replace('http://', '', $siteurl);
		$siteurl = str_replace('https://', '', $siteurl);

		$current = str_replace($siteurl, '', $current);
		$current = trim($current, '/');

		// Exact match and different URL, skip
		if ($rule->at_type == 'exact' && ($rule->at_url != $current))
		{
			return false;
		}

		// Regex URL and it doesn't match, skip
		if ($rule->at_type == 'regex' && (preg_match('#'.$rule->at_url.'#', $current) === false))
		{
			return false;
		}

		// No param speficied? This means that WAF is disabled for the whole URL, no matter what
		if (!$rule->at_param || !$rule->at_value)
		{
			return true;
		}

		// Ok, let's double check if rule param is inside the request and has the request value
		$value = $this->input->get($rule->at_param, '', 'raw');

		if ($value == $rule->at_value)
		{
			return true;
		}

		return false;
	}

	/**
	 * Execute a feature which is already loaded.
	 *
	 * @param       $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	protected function runFeature($name, array $arguments)
	{
		// Something went really bad while trying to fetch the database object. Let's stop the execution or we will break the site
		if (!$this->db)
		{
			return null;
		}

		if (!isset($this->featuresPerHook[$name]))
		{
			return null;
		}

		foreach ($this->featuresPerHook[$name] as $plugin)
		{
			if (method_exists($plugin, $name))
			{
				// Call_user_func_array is ~3 times slower than direct method calls.
				// See the on-line PHP documentation page of call_user_func_array for more information.
				switch (count($arguments))
				{
					case 0 :
						$result = $plugin->$name();
						break;
					case 1 :
						$result = $plugin->$name($arguments[0]);
						break;
					case 2:
						$result = $plugin->$name($arguments[0], $arguments[1]);
						break;
					case 3:
						$result = $plugin->$name($arguments[0], $arguments[1], $arguments[2]);
						break;
					case 4:
						$result = $plugin->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
						break;
					case 5:
						$result = $plugin->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
						break;
					default:
						// Resort to using call_user_func_array for many segments
						$result = call_user_func_array([$plugin, $name], $arguments);
				}
			}
		}

		return $result;
	}
}
