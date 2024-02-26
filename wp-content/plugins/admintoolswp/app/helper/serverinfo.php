<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

defined('ADMINTOOLSINC') or die;

class ServerInfo
{
	const APACHE = 1;
	const LITESPEED = 4;

	private $handler;
	private $software;
	private $softwareName;

	public static function getEnvironment()
	{
		$serverInfo = new self;
		$sapi = php_sapi_name();

		if (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false)
		{
			$serverInfo->setSoftware(self::APACHE);
			$serverInfo->setSoftwareName('apache');
		}

		if (stripos($_SERVER['SERVER_SOFTWARE'], 'litespeed') !== false || $sapi == 'litespeed')
		{
			$serverInfo->setSoftware(self::LITESPEED);
			$serverInfo->setSoftwareName('litespeed');
		}

		$serverInfo->setHandler($sapi);

		if ($serverInfo->isApacheModPHP())
		{
			return 'apache-mod_php';
		}

		if ($serverInfo->isApacheSuPHP())
		{
			return 'apache-suphp';
		}

		if ($serverInfo->isApache() && !$serverInfo->isApacheSuPHP() &&	($serverInfo->isCGI() || $serverInfo->isFastCGI()))
		{
			return 'cgi';
		}

		if ($serverInfo->isLiteSpeed())
		{
			return 'litespeed';
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isApache()
	{
		return $this->getSoftware() === self::APACHE;
	}

	/**
	 * @return bool
	 */
	public function isLiteSpeed() {
		return $this->getSoftware() === self::LITESPEED;
	}

	/**
	 * @return bool
	 */
	public function isApacheModPHP()
	{
		return $this->isApache() && function_exists('apache_get_modules');
	}

	/**
	 * Not sure if this can be implemented at the PHP level.
	 * @return bool
	 */
	public function isApacheSuPHP()
	{
		return $this->isApache() && $this->isCGI() &&
			function_exists('posix_getuid') &&
			getmyuid() === posix_getuid();
	}

	/**
	 * @return bool
	 */
	public function isCGI()
	{
		return !$this->isFastCGI() && stripos($this->getHandler(), 'cgi') !== false;
	}

	/**
	 * @return bool
	 */
	public function isFastCGI()
	{
		return stripos($this->getHandler(), 'fastcgi') !== false || stripos($this->getHandler(), 'fpm-fcgi') !== false;
	}

	/**
	 * @return mixed
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * @param mixed $handler
	 */
	public function setHandler($handler)
	{
		$this->handler = $handler;
	}

	/**
	 * @return mixed
	 */
	public function getSoftware()
	{
		return $this->software;
	}

	/**
	 * @param mixed $software
	 */
	public function setSoftware($software)
	{
		$this->software = $software;
	}

	/**
	 * @return mixed
	 */
	public function getSoftwareName()
	{
		return $this->softwareName;
	}

	/**
	 * @param mixed $softwareName
	 */
	public function setSoftwareName($softwareName)
	{
		$this->softwareName = $softwareName;
	}
}
