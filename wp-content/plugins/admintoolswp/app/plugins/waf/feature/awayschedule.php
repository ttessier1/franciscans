<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Wordpress;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureAwayschedule extends AtsystemFeatureAbstract
{
	protected $loadOrder = 70;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (!Wordpress::is_admin())
		{
			return false;
		}

		if (!$this->cparams->getValue('awayschedule_from') || !$this->cparams->getValue('awayschedule_to'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Checks if the secret word is set in the URL query, or redirects the user
	 * back to the home page.
	 */
	public function onSystem()
	{
		$timezone = Wordpress::get_timezone_string();

		$now  = new DateTime('now', new DateTimeZone($timezone));
		$from = new DateTime($this->cparams->getValue('awayschedule_from'), new DateTimeZone($timezone));
		$to   = new DateTime($this->cparams->getValue('awayschedule_to'), new DateTimeZone($timezone));

		// Wait, FROM is later than TO? This means that the user set an interval like this: 17:30 - 11:00
		// Let's move the FROM constrain one day back
		if($from > $to)
		{
			$from = $from->modify('-1 day');
		}

		// Login attempt, while we set the away schedule, let's ban the user
		if ($now > $from && $now < $to)
		{
			$this->redirectToHome();
		}
	}
}
