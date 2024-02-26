<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

defined('ADMINTOOLSINC') or die;

class EmergencyOffline extends Controller
{
	public function offline()
	{
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\EmergencyOffline $model */
		$model = $this->getModel();

		if ($model->putOffline())
		{
			$msg = Language::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_APPLIED');
			$type = 'info';
		}
		else
		{
			$msg = Language::_('COM_ADMINTOOLS_ERR_EMERGENCYOFFLINE_NOTAPPLIED');
			$type = 'error';
		}

		$this->getView()->enqueueMessage($msg, $type);
		$this->redirect(ADMINTOOLSWP_URL);
	}

	public function online()
	{
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\EmergencyOffline $model */
		$model  = $this->getModel();
		$status = $model->putOnline();

		if ($status)
		{
			$msg = Language::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_UNAPPLIED');
			$type = 'info';
		}
		else
		{
			$msg = Language::_('COM_ADMINTOOLS_ERR_EMERGENCYOFFLINE_NOTUNAPPLIED');
			$type = 'error';
		}

		$this->getView()->enqueueMessage($msg, $type);
		$this->redirect(ADMINTOOLSWP_URL);
	}
}
