<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller\Mixin;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\ConfigureWAF;

defined('ADMINTOOLSINC') or die;

trait SendTroubleshootingEmail
{
	/**
	 * Sends a preemptive troubleshooting email to the user before taking an action which might lock them out.
	 *
	 * @param   string  $controllerName
	 *
	 * @return  void
	 */
	protected function sendTroubelshootingEmail($controllerName)
	{
		// Is sending this email blocked in the WAF configuration?
		/** @var ConfigureWAF $configModel */
		$configModel = new ConfigureWAF($this->input);
		$wafConfig   = $configModel->getItems();
		$sendEmail   = isset($wafConfig['sendTroubleshootingEmail']) ? $wafConfig['sendTroubleshootingEmail'] : 1;

		if (!$sendEmail)
		{
			return;
		}

		// Construct the email
		$user      = wp_get_current_user();
		$siteName  = get_bloginfo('name');
		$actionKey = strtoupper('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_ACTION_' . $controllerName);
		$action    = Language::_($actionKey);
		$subject   = Language::_('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_SUBJECT');
		$body      = Language::sprintf('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_HELLO', $user->user_login) . "\n\n" .
			Language::sprintf('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_DESCRIPTION', $action, $siteName) . "\n\n" .
			"-  http://akee.ba/wplockedout\n" .
			"-  http://akee.ba/wp500htaccess\n" .
			"-  http://akee.ba/wpadminpassword\n" .
			"-  http://akee.ba/wp403edituser\n\n" .
			Language::_('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_SUPPORT') . "\n\n" .
			Language::_('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_WHOSENTTHIS') . "\n" .
			str_repeat('=', 40) . "\n\n" .
			Language::_('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_WHOSENT_1') . "\n\n" .
			Language::_('COM_ADMINTOOLS_TROUBLESHOOTEREMAIL_BODY_WHOSENT_2') . "\n";
		$body      = wordwrap($body);

		// Can't send email if I don't about this controller
		if ($action == $actionKey)
		{
			return;
		}

		// Send the email immediately, do not enqueue
		Wordpress::sendEmail([$user->user_email], $subject, $body, false, false);
	}
}