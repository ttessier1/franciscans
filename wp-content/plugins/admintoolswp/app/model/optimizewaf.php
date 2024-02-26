<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\HtaccessManager;
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class OptimizeWaf extends Model
{
	/** @var 	string	Environment we're going to work with */
	private $environment;

	/** @var	array	List of supported environments */
	private $supported = array('apache-mod_php', 'apache-suphp', 'cgi', 'litespeed');

	/**
	 * Is Admin Tools running in auto-prepend mode?
	 *
	 * @return bool
	 */
	public function autoPrependEnabled()
	{
		return defined('ADMINTOOLSWP_AUTOPREPEND');
	}

	/**
	 * Let's double check that if we have all files in place, we are actually running in auto-prepend mode
	 *
	 * @return bool
	 */
	public function correctlyConfigured()
	{
		// If we have such constant, it means that we're already good
		if (defined('ADMINTOOLSWP_AUTOPREPEND'))
		{
			return true;
		}

		// No bootstrap file that should be auto-prepended? Well, it means that the user didn't enabled it, so we're good
		if (!file_exists(ABSPATH.'admintools-waf.php'))
		{
			return true;
		}

		// If I'm here it means that we have the auto-prepend entry point but we're not in auto-prepend mode.
		// Delete any saved environment info
		$this->saveEnvironment(null);

		// Something is going on, let's inform the user.
		return false;
	}

	/**
	 * Enables the auto-prepend mode, by creating the entry point and modifying server files accordingly to the environment
	 * currently used (ie .htaccess, .user.ini etc etc)
	 *
	 * @param	string	$environment	The environment we're going to create the file for
	 */
	public function enableAutoPrepend($environment = null)
	{
		// If an environment wasn't passed as argument, let's try to load it from the storage
		if (is_null($environment))
		{
			$params		 = Params::getInstance();
			$environment = $params->getValue('optimizewaf.environment', null);
		}

		$this->environment = $environment;

		if (!in_array($this->environment, $this->supported))
		{
			throw new \InvalidArgumentException(Language::sprintf('COM_ADMINTOOLS_OPTIMIZEWAF_INVALID_ENV', $environment));
		}

		$waf_file = ABSPATH.'admintools-waf.php';

		$this->createAutoPrependFile($waf_file);

		$manager = HtaccessManager::getInstance();

		// Current environment supports htaccess directives?
		$htaccess_rules = $this->getHtaccessRules($waf_file);

		// Do I have to protect the user PHP ini?
		$userIni = ini_get('user_ini.filename');

		if ($userIni)
		{
			$userIni = addslashes($userIni);

			$htaccess_rules .= <<<HTACCESS

<Files "$userIni">
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Order deny,allow
	Deny from all
</IfModule>
</Files>
HTACCESS;
		}

		$manager->setOption('OptimizeWaf', $htaccess_rules);

		if (!$manager->updateFile())
		{
			throw new \RuntimeException(Language::sprintf('COM_ADMINTOOLS_ERR_FILESYSTEM_WRITE', '.htaccess'));
		}

		// Do I have to update the user_ini file, too?
		$iniRules = $this->getUserIniRules($waf_file);

		// Ok, nothing more to do
		if (!$iniRules)
		{
			// Let's save the environment
			$this->saveEnvironment($this->environment);

			return;
		}

		$contents = $iniRules;
		$oldIni   = get_home_path().$userIni;

		if (file_exists($oldIni))
		{
			$contents = file_get_contents($oldIni);
			$contents = str_replace('auto_prepend_file', ';auto_prepend_file', $contents);
			$regex = '/; AdminTools WAF.*?; END AdminTools WAF/is';
			if (preg_match($regex, $contents, $matches))
			{
				$contents = preg_replace($regex, $iniRules, $contents);
			} else
			{
				$contents .= "\n\n" . $iniRules;
			}
		}

		if (!file_put_contents($oldIni, $contents))
		{
			throw new \RuntimeException(Language::sprintf('COM_ADMINTOOLS_ERR_FILESYSTEM_WRITE', $oldIni));
		}

		// Let's save the environment
		$this->saveEnvironment($this->environment);
	}

	private function getHtaccessRules($waf_file)
	{
		switch ($this->environment)
		{
			case 'apache-mod_php':
				$version = PHP_MAJOR_VERSION;
				$rule = <<<HTACCESS
<IfModule mod_php$version.c>
	php_value auto_prepend_file '$waf_file'
</IfModule>
HTACCESS;
				return $rule;

			case 'litespeed':
				$rule = <<<HTACCESS
<IfModule LiteSpeed>
	php_value auto_prepend_file '$waf_file'
</IfModule>
<IfModule lsapi_module>
	php_value auto_prepend_file '$waf_file'
</IfModule>
HTACCESS;
				return $rule;

			default:
				return '';
		}
	}

	public function createAutoPrependFile($waf_file)
	{
		// Let's create a relative path for the include/require, so Admin Tools won't die after a server change
		$relative_path = str_replace(ABSPATH, '', ADMINTOOLSWP_PATH);

		$run_plugins = $relative_path.'/helpers/runplugins.php';
		$app_root    = $relative_path;

		$contents = file_get_contents(ADMINTOOLSWP_PATH.'/app/assets/optimizewaf/admintools.php');

		$contents = str_replace('##RUNPLUGINS##', $run_plugins, $contents);
		$contents = str_replace('##VERSION##', ADMINTOOLSWP_VERSION, $contents);
		$contents = str_replace('##ADMINTOOLSPATH##', $app_root, $contents);

		file_put_contents($waf_file, $contents);
	}

	private function getUserIniRules($waf_file)
	{
		switch ($this->environment)
		{
			case 'apache-suphp':
			case 'cgi':
			case 'litespeed':
				$ini = <<<INI
; AdminTools WAF
auto_prepend_file = '$waf_file'
; END AdminTools WAF
INI;

				return $ini;
			default:
				return '';
		}
	}

	/**
	 * Disables the auto-prepend mode, by removing all the files and clening up the .htaccess / user PHP ini file
	 */
	public function disableAutoPrepend()
	{
		// First update the .htaccess file
		$manager = HtaccessManager::getInstance();
		$manager->setOption('OptimizeWaf', null);

		if (!$manager->updateFile())
		{
			throw new \RuntimeException(Language::_('COM_ADMINTOOLS_OPTIMIZEWAF_ERR_UPDATE_HTACCESS'));
		}

		// Then update the user PHP ini (if required)
		$userIni = ini_get('user_ini.filename');

		// If Admin Tools directive are found, simply comment then and write back to the file
		if ($userIni)
		{
			$userIni = get_home_path().$userIni;

			if (file_exists($userIni))
			{
				$contents = file_get_contents($userIni);

				if (stripos($contents, 'AdminTools WAF') !== false)
				{
					$contents = str_replace('auto_prepend', ';auto_prepend', $contents);

					file_put_contents($userIni, $contents);
				}
			}
		}

		// Finally, if everything went fine, let's try to delete the entry point
		$waf_file = ABSPATH.'admintools-waf.php';

		if (file_exists($waf_file))
		{
			if (!unlink($waf_file))
			{
				throw new \RuntimeException(Language::sprintf('COM_ADMINTOOLS_OPTIMIZEWAF_ERR_DELETE_FILE', $waf_file));
			}
		}

		// Deletes any saved environment
		$this->saveEnvironment(null);
	}

	/**
	 * Stored the environment inside Admin Tools params, so we can later re-generate the file
	 *
	 * @param	null|string		$environment	Server environment that we're using. Null for deleting it
	 */
	public function saveEnvironment($environment = null)
	{
		$params = Params::getInstance();
		$params->setValue('optimizewaf.environment', $environment, true);
	}
}
