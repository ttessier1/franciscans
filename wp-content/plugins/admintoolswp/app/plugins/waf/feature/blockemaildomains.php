<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureBlockemaildomains extends AtsystemFeatureAbstract
{
	protected $loadOrder = 930;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		$domains = $this->cparams->getValue('blockedemaildomains', '');

		if (empty($domains))
		{
			return false;
		}

		return true;
	}

	/**
	 * @param   \WP_Error   $errors
	 * @param   string      $sanitized_user_login
	 * @param   string      $user_email
	 *
	 * @return \WP_Error
	 */
	public function onUserBeforeRegister($errors, $sanitized_user_login, $user_email)
	{
		$allowed = false;
		$block   = ($this->cparams->getValue('filteremailregistration', 'block') == 'block');
		$domains = $this->cparams->getValue('blockedemaildomains', '');

		if (!is_array($domains))
		{
			$domains = str_replace("\r", "\n", $domains);
			$domains = str_replace("\n\n", "\n", $domains);
			$domains = explode("\n", $domains);
		}

		$domains = array_filter($domains);
		$domains = array_unique($domains);

		foreach ($domains as $domain)
		{
			// The user used a blocked domain, let's prevent
			// Block specific domains and we have a match
			if ($block && (stripos($user_email, trim($domain)) !== false))
			{
				$errors->add('amdintoolswp_blockeddomain', Language::sprintf('COM_ADMINTOOLS_ERR_BLOCKEDEMAILDOMAINS', $domain));
			}

			// Allow only specific domains and the user is using a domain that is NOT in the list
			if (!$block && (stripos($user_email, trim($domain)) !== false))
			{
				// Let's raise the flag to mark that we got a match
				$allowed = true;
			}
		}

		// If I have to allow only specific email domains and we didn't have a match, let's block the registration
		if (!$block && !$allowed)
		{
			$errors->add('amdintoolswp_blockeddomain', Language::sprintf('COM_ADMINTOOLS_ERR_BLOCKEDEMAILDOMAINS', $user_email));
		}

		return $errors;
	}
}
