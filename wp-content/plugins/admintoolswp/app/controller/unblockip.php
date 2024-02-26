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

class UnblockIP extends Controller
{
	public function unblock()
	{
		// CSRF prevention
		$this->csrfProtection();

		$ip = $this->input->getString('ip', '');

		/** @var \Akeeba\AdminTools\Admin\Model\UnblockIP $model */
		$model = $this->getModel();

		$status = $model->unblockIP($ip);

		if ($status)
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_OK'));
		}
		else
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_NOTFOUND'), 'warning');
		}

		$this->redirect(ADMINTOOLSWP_URL . '&view=UnblockIP');
	}
}
