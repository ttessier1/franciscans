<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\HtaccessManager;
use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Library\Uri\Uri;

class ConfigureWAF extends Model
{
	/**
	 * Default configuration variables
	 *
	 * @var array
	 */
	private $defaultConfig = [
		'ipworkarounds'               => 0,
		'ipwl'                        => 0,
		'ipbl'                        => 0,
		'nonewadmins'                 => 0,
		'sqlishield'                  => 1,
		'antispam'                    => 0,
		'custgenerator'               => 0,
		'generator'                   => '',
		'loginerrormsg'               => '',
		'logbreaches'                 => 1,
		'emailonadminlogin'           => '',
		'emailonfailedadminlogin'     => '',
		'emailbreaches'               => '',
		'csrfshield'                  => 0,
		'rfishield'                   => 1,
		'phpshield'                   => 1,
		'dfishield'                   => 0,
		'badbehaviour'                => 0,
		'bbstrict'                    => 0,
		'bbhttpblkey'                 => '',
		'bbwhitelistip'               => '',
		'tsrenable'                   => 0,
		'tsrstrikes'                  => 3,
		'tsrnumfreq'                  => 1,
		'tsrfrequency'                => 'minute',
		'tsrbannum'                   => 15,
		'tsrbanfrequency'             => 'minute',
		'spammermessage'              => 'You are a spammer, hacker or an otherwise bad person.',
		'uploadshield'                => 1,
		'neverblockips'               => '',
		'emailafteripautoban'         => '',
		'custom403msg'                => '',
		'httpblenable'                => 0,
		'httpblthreshold'             => 25,
		'httpblmaxage'                => 30,
		'httpblblocksuspicious'       => 0,
		'trackfailedlogins'           => 1,
		'use403view'                  => 0,
		'iplookup'                    => 'ip-lookup.net/index.php?ip={ip}',
		'iplookupscheme'              => 'https',
		'saveusersignupip'            => 0,
		'whitelist_domains'           => '.googlebot.com,.search.msn.com',
		'reasons_nolog'               => 'geoblocking',
		'reasons_noemail'             => 'geoblocking',
		'email_throttle'              => 1,
		'permaban'                    => 0,
		'permabannum'                 => 0,
		'awayschedule_from'           => '',
		'awayschedule_to'             => '',
		'adminlogindir'               => '',
		'adminlogindir_action'        => 1,
		'customregister'              => '',
		'disablexmlrpc'               => 0,
		// PLEASE NOTE: Previously this field was used only to BLOCK email domains,
		// but now is used to hold the list of blocked OR allowed domains.
		'blockedemaildomains'         => '',
		'selfprotect'                 => 0,
		'sessionnumduration'          => '',
		'sessionduration'             => '',
		'sessionnumduration_remember' => '',
		'sessionduration_remember'    => '',
		'removerss'                   => 0,
		'removeblogclient'            => 0,
		'passexp'                     => 0,
		'passexp_roles'               => [],
		'error_reporting'             => '',
		'logfile'                     => 0,
		'filteremailregistration'     => 'block',
		'criticalfiles'               => 0,
		'leakedpwd'					  => 0,
		'leakedpwd_roles'			  => [],
		'logusernames'                => 0,
		'troubleshooteremail'         => 1,
		// Auto-update options
		'core_autoupdates'            => 1,
		'autoupdate_plugins'          => 1,
		'autoupdate_themes'           => 1,
		'autoupdate_translations'     => 1,
		'disable_image_scaling'       => 0,
	];

	/** @var array	List of fields that are stored as imploded array (comma separated strings) */
	private $imploded_array = [
		'reasons_nolog',
		'reasons_noemail',
		'passexp_roles',
		'leakedpwd_roles'
	];

	/**
	 * Load the WAF configuration
	 *
	 * @return  array
	 */
	public function getItems($overrideLimits = false, $limitstart = 0, $limit = 0)
	{
		$params = Storage::getInstance();

		$config = [];

		foreach ($this->defaultConfig as $k => $v)
		{
			$value = $params->getValue($k, $v);

			// Automatically explode comma separated fields
			if (in_array($k, $this->imploded_array) && is_string($value))
			{
				$value = explode(',', $value);
			}

			$config[ $k ] = $value;
		}

		$this->migrateIplookup($config);
		$this->fillLeakedPwdRoles($config);

		return $config;
	}

	/**
	 * Merge and save $newParams into the WAF configuration
	 *
	 * @param   array  $newParams  New parameters to save
	 *
	 * @return  void
	 */
	public function saveConfig(array $newParams)
    {
        $this->migrateIplookup($newParams);

        $params = Storage::getInstance();

        foreach ($newParams as $key => $value) {
            // Do not save unnecessary parameters
            if (!array_key_exists($key, $this->defaultConfig)) {
                continue;
            }

            if (($key == 'awayschedule_from') || ($key == 'awayschedule_to')) {
                // Sanity check for Away Schedule time format
                if (!preg_match('#^([0-1]?[0-9]|[2][0-3]):([0-5][0-9])$#', $value)) {
                    $value = '';
                }
            }

            // Implode special fields
            if (in_array($key, $this->imploded_array)) {
                if (empty($value)) {
                    $value = '';
                } elseif (is_array($value)) {
                    $value = implode(',', $value);
                }
            }

            $params->setValue($key, $value);
        }

        $params->setValue('quickstart', 1);

        $params->save();

        $contents = null;

        // User wants to change WordPress backend, we have to do some tricks
        if ($params->getValue('adminlogindir')) {
            $contents = $this->changeWpBackend();
        }

        // Reapply the .htaccess maker in case the admin login dir has changed
        if (!is_null($contents))
        {
            if (ServerTechnology::isHtaccessSupported())
            {
                $this->ApplyAdminLoginDirInHtaccess($contents);
            }
            elseif (ServerTechnology::isWebConfigSupported())
            {
                // TODO Reapply web.config Maker
            }
            elseif (ServerTechnology::isNginxSupported())
            {
                // TODO Reapply nginx.conf Maker
            }
        }
	}

    /**
     * Return the .htaccess, web.config or nginx.conf code required to change the Wordprss administration directory.
     *
     * @return  array|null
     */
	public function changeWpBackend()
	{
		$params   = Storage::getInstance();
		$admindir = $params->getValue('adminlogindir');
		$registerSlug = $params->getValue('customregister');

		// mhm... no new directory? No need to work
		if (!$admindir)
		{
			return null;
		}

		$uri       = Uri::getInstance(Wordpress::get_option('site_url'));
		$regexPath = $uri->getPath() ? '(/'.$uri->getPath().'/)?' : '';
		$redirectPath = $uri->getPath() ? '/'.$uri->getPath().'/' : '';

		if (ServerTechnology::isHtaccessSupported())
        {
            // RewriteRule ^(/wordpress/)?test/?$ /wordpress/wp-login.php [QSA,L]
            $contents[] = sprintf("RewriteRule ^%s%s/?$ %swp-login.php [QSA,L]", $regexPath, $admindir, $redirectPath);

            if ($registerSlug)
            {
                // RewriteRule ^(/wordpress/)?wp-register-php/?$ /test?action=register [QSA,L]
                $contents[] = sprintf("RewriteRule ^%s%s/?$ /%s?action=register [QSA,L]", $regexPath, $registerSlug, $admindir);
            }

            return $contents;
        }
        elseif (ServerTechnology::isWebConfigSupported())
        {
            // TODO Create web.config rules
        }
        elseif (ServerTechnology::isNginxSupported())
        {
            // TODO Create nginx.conf rules
        }

        return null;
	}

	/**
	 * Used to transparently set the IP lookup service to a sane default when none is specified
	 *
	 * @param   array  $data  The configuration data we'll modify
	 *
	 * @return  void
	 */
	private function migrateIplookup(&$data)
	{
		$iplookup       = $data['iplookup'];
		$iplookupscheme = $data['iplookupscheme'];

		if (empty($iplookup))
		{
			$iplookup       = 'ip-lookup.net/index.php?ip={ip}';
			$iplookupscheme = 'http';
		}

		$test = strtolower($iplookup);
		if (substr($test, 0, 7) == 'http://')
		{
			$iplookup       = substr($iplookup, 7);
			$iplookupscheme = 'http';
		}
		elseif (substr($test, 0, 8) == 'https://')
		{
			$iplookup       = substr($iplookup, 8);
			$iplookupscheme = 'https';
		}

		$data['iplookup']       = $iplookup;
		$data['iplookupscheme'] = $iplookupscheme;
	}

	/**
	 * If empty, fills the groups where we should check for leaked passwords
	 *
	 * @param	array	$data	The configuration data we'll modify
	 */
	private function fillLeakedPwdRoles(&$data)
	{
		// Already filled, nothing to do
		if ($data['leakedpwd_roles'])
		{
			return;
		}

		// Let's see if we already calculated them previously
		$params		 = Storage::getInstance();
		$admin_roles = $params->getValue('default_admin_roles', []);

		if ($admin_roles)
		{
			$data['leakedpwd_roles'] = $admin_roles;

			return;
		}

		// Ok, I don't have any. Let's get them
		$roles     = [];
		$all_roles = wp_roles()->roles;

		foreach ($all_roles as $key => $role)
		{
			if (isset($role['capabilities']['activate_plugins']) && $role['capabilities']['activate_plugins'])
			{
				$roles[] = $key;
			}
		}

		$data['leakedpwd_roles'] = $roles;

		$params->setValue('default_admin_roles', $roles);
		$params->save();
	}

    /**
     * Applies the changed administrator login directory in the .htaccess file. The code is placed inside the same
     * markers used for the .htaccess Maker but is NOT part of the .htaccess Maker proper.
     *
     * @param   array  $contents  The .htaccess lines used to implement the new directory in the .htaccess file
     *
     * @return  void
     */
    public function ApplyAdminLoginDirInHtaccess($contents)
    {
        try
        {
            $htaccess = HtaccessManager::getInstance();
        }
        catch (\RuntimeException $e)
        {
            return;
        }

        $htaccess->setOption('adminlogindir', $contents);
        $htaccess->updateFile();
    }
}
