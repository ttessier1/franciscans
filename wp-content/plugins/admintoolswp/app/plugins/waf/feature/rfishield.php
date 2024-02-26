<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Wordpress;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureRfishield extends AtsystemFeatureAbstract
{
	protected $loadOrder = 350;

	private $siteurl = '';
	private $home    = '';

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (Wordpress::is_admin() && !Wordpress::is_ajax())
		{
			return false;
		}

		if ($this->skipFiltering)
		{
			return false;
		}

		return ($this->cparams->getValue('rfishield', 1) == 1);
	}

	/**
	 * Simple Remote Files Inclusion block. If any query string parameter contains a reference to an http[s]:// or ftp[s]://
	 * address it will be scanned. If the remote file looks like a PHP script, we block access.
	 */
	public function onSystem()
	{
		$hashes = array('get', 'post');
		$regex = '#(http|ftp){1,1}(s){0,1}://.*#i';

		$this->siteurl = Wordpress::get_option('siteurl');
		$this->home    = Wordpress::get_option('home');

		// Remove the protocol from the base URLs
		$this->siteurl = str_replace(array('http://', 'https://'), '', $this->siteurl);
		$this->home    = str_replace(array('http://', 'https://'), '', $this->home);

		foreach ($hashes as $hash)
		{
			$input   = $this->input->$hash;
			$allVars = $input->getData();

			if (empty($allVars))
			{
				continue;
			}

			if ($this->match_array_and_scan($regex, $allVars))
			{
				$extraInfo = "Hash      : $hash\n";
				$extraInfo .= "Variables :\n";
				$extraInfo .= print_r($allVars, true);
				$extraInfo .= "\n";

				$this->exceptionsHandler->blockRequest('rfishield', null, $extraInfo);
			}
		}
	}

	private function match_array_and_scan($regex, $array)
	{
		$result = false;

		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				if (!empty($this->exceptions) && in_array($key, $this->exceptions))
				{
					continue;
				}

				if (is_array($value))
				{
					$result = $this->match_array_and_scan($regex, $value);
				}
				else
				{
					$result = preg_match($regex, $value);

					// In WP redirects are not base64 encoded, this means that we have to check if we are referring
					// to ourselves and stop, otherwise we'll be stuck in a infinite recursion
					// We have to remove the protcol, otherwise if we use a different protocol than the one stored inside
					// WP options (ie HTTPS when HTTP is stored) we end up in a infinite recursion
					$value = str_replace(array('http://', 'https://'), '', $value);

					if (stripos($value, $this->siteurl) === 0 || stripos($value, $this->home) === 0)
					{
						$result = false;
					}
				}

				if ($result)
				{
					// Can we fetch the file directly?
					$fContents = @file_get_contents($value);

					if (!empty($fContents))
					{
						$result = (strstr($fContents, '<?php') !== false);

						if ($result)
						{
							break;
						}
					}
					else
					{
						$result = false;
					}
				}
			}
		}
		elseif (is_string($array))
		{
			$result = preg_match($regex, $array);

			if ($result)
			{
				// Can we fetch the file directly?
				$fContents = @file_get_contents($array);

				if (!empty($fContents))
				{
					$result = (strstr($fContents, '<?php') !== false);

					if ($result)
					{
						return $result;
					}
				}
				else
				{
					$result = false;
				}
			}
		}

		return $result;
	}
}
