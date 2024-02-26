<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureDeleteusermeta extends AtsystemFeatureAbstract
{
	protected $loadOrder = 890;

	/**
	 * Hooks to the logout event to delete temporary user meta
	 */
	public function onUserLogout()
	{
		$user_id = get_current_user_id();

		$this->delete_user_meta($user_id);
	}

	/**
	 * Hooks on cookie expiration to delete temporary user meta
	 *
	 * @param   array   $cookie_params
	 */
	public function onUserCookieExpired($cookie_params)
	{
		// Missing info, let's stop here
		if (!$cookie_params || !isset($cookie_params['username']))
		{
			return;
		}

		/** @var \WP_User $user */
		$user = get_user_by('login', $cookie_params['username']);

		// For some reason I couldn't fetch the user, let's stop here
		if (!$user)
		{
			return;
		}

		$this->delete_user_meta($user->ID);

	}

	private function delete_user_meta($user_id)
	{
		// This should never happen, but better be safe than sorry
		if (!$user_id)
		{
			return;
		}

		delete_user_meta($user_id, 'admintoolswp_storage');
	}
}
