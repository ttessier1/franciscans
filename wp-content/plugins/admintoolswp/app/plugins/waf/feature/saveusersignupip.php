<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Utils\Ip;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureSaveusersignupip extends AtsystemFeatureAbstract
{
	protected $loadOrder = 910;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		// Registration not enabled? No need to continue
		if (!Wordpress::get_option('users_can_register'))
		{
			return false;
		}

		if ($this->cparams->getValue('saveusersignupip', 0) != 1)
		{
			return false;
		}

		// If we're in auto-prepend mode we don't have to run
		if (!function_exists('add_action'))
		{
			return false;
		}

		// If we're here it means that we are enabled. Let's hook our function to display the save date
		add_action('edit_user_profile', array($this, 'displayExtraFields'));
		add_action('show_user_profile', array($this, 'displayExtraFields'));

		return true;
	}

	/**
	 * Automatically saves the IP address and the User-Agent used for signup
	 *
	 * @param   int $user_id
	 */
	public function onUserAfterSave($user_id)
	{
		// Get the IP address
		$ip = Ip::getIp();

		if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
		{
			$ip = substr($ip, strrpos($ip, ':') + 1);
		}

		// Get the user agent string
		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		update_user_meta($user_id, 'admintoolswp_signup_ip', $ip);
		update_user_meta($user_id, 'admintoolswp_user_agent', $user_agent);
	}

	/**
	 * Public function used to display the additional fields saved during user signup
	 *
	 * @param   WP_User $user
	 */
	public function displayExtraFields($user)
	{
		$ip_label = Language::_('COM_ADMINTOOLS_SIGNUPIP');
		$ip       = get_user_meta($user->ID, 'admintoolswp_signup_ip', true);
		$ua_label = Language::_('COM_ADMINTOOLS_SIGNUPUA');
		$ua       = get_user_meta($user->ID, 'admintoolswp_user_agent', true);

		$html = <<<HTML
<h3>AdminTools for WordPress</h3>
<table class="form-table">
	<tr>
    	<th><label>$ip_label</label></th>
      	<td>$ip</td>
	</tr>
	<tr>
    	<th><label>$ua_label</label></th>
      	<td>$ua</td>
	</tr>
</table>
HTML;

		echo $html;
	}
}
