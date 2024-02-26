<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;

class AtsystemFeaturePassexpiration extends AtsystemFeatureAbstract
{
	protected $loadOrder = 40;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		$days  = $this->cparams->getValue('passexp', '');
		$roles = $this->cparams->getValue('passexp_roles', '');

		// User must set an expiration time and roles to enable this feature
		return ($days && $roles);
	}

	/**
	 * Hooks to several WordPress events in order to save the last time the password was changed and to get if the user
	 * needs to reset his password
	 */
	public function onCustomHooks()
	{
		// Events where the password could be changed. We have to reset our timer
		// Please note: we're going to save this info for all roles. We will perform the check only vs
		// the specified roles, so the user can add/remove roles without forcing users to change the password
		// before desired expiration
		add_action('profile_update', array($this, 'profile_update'), 10, 2);
		add_action('user_register' , array($this, 'user_register'), 10, 1);
		add_action('password_reset', array($this, 'password_reset'), 10, 2);

		// Double check the user is not re-using the same old password
		add_action('validate_password_reset', array($this, 'validate_password_reset'), 10, 2);

		// Actual check for password expiration
		add_action('wp_login', array($this, 'wp_login'), 10, 2);

		// Custom message while resetting the password
		add_filter('login_message', array($this, 'reset_password_message'), 99);
	}

	/**
	 * Fired after WordPress correctly updates a user profile
	 *
	 * @param 	int			$user_id
	 * @param	WP_User		$old_user_data
	 */
	public function profile_update($user_id, $old_user_data)
	{
		// User didn't change his password
		$new_pass = $this->input->get('pass1', '');

		if ($new_pass == '')
		{
			return;
		}

		// Ok, but the password is different than the stored one?
		$unchanged = wp_check_password($new_pass, $old_user_data->user_pass);

		if (!$unchanged)
		{
			$this->savePasswordChange($user_id);
		}
	}

	/**
	 * Fired when a new user is registered
	 *
	 * @param	string	$user_id
	 */
	public function user_register($user_id)
	{
		$this->savePasswordChange($user_id);
	}

	/**
	 * Fired when a user resets his password
	 *
	 * @param WP_User	$user
	 * @param string	$new_pass	New password being saved
	 */
	public function password_reset($user, $new_pass)
	{
		$this->savePasswordChange($user->ID);
	}

	/**
	 * @param WP_Error			$errors
	 * @param WP_User|WP_Error	$user
	 *
	 * @return void
	 */
	public function validate_password_reset($errors, $user)
	{
		// There already are some errors, better stop here
		if ($errors->get_error_code() || $user instanceof WP_Error)
		{
			return;
		}

		$pass1 = $this->input->getCmd('pass1', '');
		$pass2 = $this->input->getCmd('pass2', '');

		$roles = $this->cparams->getValue('passexp_roles', array());
		$roles = explode(',', $roles);

		if (!$pass1 || !$pass2 || ($pass1 != $pass2))
		{
			return;
		}

		// This should never happen, but better be safe than sorry
		if (!$user->roles)
		{
			return;
		}

		// No user roles match with the settings? Let's stop here
		if (!array_intersect($user->roles, $roles))
		{
			return;
		}

		$is_same = wp_check_password($pass1, $user->user_pass);

		if ($is_same)
		{
			$errors->add('exp_pass_used', Language::_('COM_ADMINTOOLS_LBL_PASSEXPIRATION_ALREADY_USED'));
		}
	}

	/**
	 * Check if the current password is expired, if so let's force the user to reset it
	 *
	 * @param	string		$user_login
	 * @param 	WP_User		$user
	 */
	public function wp_login($user_login, $user)
	{
		$days  = $this->cparams->getValue('passexp', '');
		$roles = $this->cparams->getValue('passexp_roles', array());
		$roles = explode(',', $roles);

		// This should never happen, but better be safe than sorry
		if (!$user->roles)
		{
			return;
		}

		// No user roles match with the settings? Let's stop here
		if (!array_intersect($user->roles, $roles))
		{
			return;
		}

		// Is password still valid? Get the last change, add the expiration and check vs current time
		$lastchange = get_user_meta($user->ID, 'admintoolswp_password_change', true);

		// If we don't have a value for this user, let's save it for the next time
		if (!$lastchange)
		{
			$this->savePasswordChange($user->ID);

			return;
		}

		$expiration = strtotime('+ '.$days.' days', $lastchange);

		if (time() < $expiration)
		{
			return;
		}

		// Destroy all user sessions
		wp_destroy_all_sessions();

		$login_url = wp_login_url();
		$login_url = add_query_arg(array('action' => 'lostpassword', 'atwp' => 'passexpiration'), $login_url);

		wp_safe_redirect($login_url,302);

		exit;
	}

	public function reset_password_message($message)
	{
		$action = $this->input->getCmd('action', '');
		$atwp	= $this->input->getCmd('atwp', '');

		if ($action != 'lostpassword' || $atwp != 'passexpiration')
		{
			return $message;
		}

		$days = $this->cparams->getValue('passexp', '');

		$html  = '<p id="login_error">';
		$html .= Language::sprintf('COM_ADMINTOOLS_LBL_PASSEXPIRATION_LOGIN', $days);
		$html .= '</p>';

		// Add WordPress default message
		$html .= '<p class="message">' . __('Please enter your username or email address. You will receive a link to create a new password via email.') . '</p>';

		return $html;
	}

	/**
	 * Updates user meta to store the last time the password was changed
	 *
	 * @param	int		$user_id
	 */
	private function savePasswordChange($user_id)
	{
		update_user_meta($user_id, 'admintoolswp_password_change', time());
	}
}