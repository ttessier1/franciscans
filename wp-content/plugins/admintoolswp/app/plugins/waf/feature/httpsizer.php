<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Library\Uri\Uri;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureHttpsizer extends AtsystemFeatureAbstract
{
	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		// The feature must be enabled
		if ($this->cparams->getValue('httpsizer', 0) != 1)
		{
			return false;
		}

		// Make sure we're accessed over SSL (HTTPS)
		$uri = Uri::getInstance();
		$protocol = $uri->toString(array('scheme'));

		if ($protocol != 'https://')
		{
			return false;
		}

		return true;
	}

	/**
	 * Converts all HTTP URLs to HTTPS URLs when the site is accessed over SSL
	 *
	 * @param   string  $contents   Current output buffer
	 *
	 * @return  string              New output buffer with all links converted to HTTPS
	 */
	public function onBeforeRender($contents)
	{
		$contents = str_replace('http://', 'https://', $contents);

		return $contents;
	}
}
