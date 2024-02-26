<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Controller\Mixin\SendTroubleshootingEmail;
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

class QuickStart extends Controller
{
	use SendTroubleshootingEmail;

	public function commit()
	{
		// CSRF prevention
		$this->csrfProtection();

		$this->sendTroubelshootingEmail('QuickStart');

		/** @var \Akeeba\AdminTools\Admin\Model\QuickStart $model */
		$model = $this->getModel();

		$stateVariables = array(
			'admin_username', 'admin_password', 'emailonadminlogin', 'ipwl', 'detectedip', 'nonewadmins',
			'enablewaf', 'ipworkarounds', 'autoban', 'autoblacklist', 'emailbreaches', 'bbhttpblkey', 'htmaker'
		);

		$preferences = array();

		foreach ($stateVariables as $k)
		{
			$v = $this->input->get($k, null, 'raw');
			$preferences[$k] = $v;
		}

		$model->applyPreferences($preferences);

		$message = Language::_('COM_ADMINTOOLS_QUICKSTART_MSG_DONE');
		$this->getView()->enqueueMessage($message);

		$this->redirect(ADMINTOOLSWP_URL.'&view=ControlPanel');
	}
}
