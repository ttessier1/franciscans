<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

use Akeeba\AdminTools\Admin\Controller\Mixin\SendTroubleshootingEmail;
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

defined('ADMINTOOLSINC') or die;

class AdminPassword extends Controller
{
	use SendTroubleshootingEmail;

	public function protect()
	{
		$this->csrfProtection();

		$username        = $this->input->get('username', '', 'raw');
		$password        = $this->input->get('password', '', 'raw');
		$password2       = $this->input->get('password2', '', 'raw');
		$resetErrorPages = $this->input->get('resetErrorPages', 1, 'int');

		if (empty($username))
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOUSERNAME'), 'error');
			$this->redirect(ADMINTOOLSWP_URL . '&view=AdminPassword');
		}

		if (empty($password))
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOPASSWORD'), 'error');
			$this->redirect(ADMINTOOLSWP_URL . '&view=AdminPassword');
		}

		if ($password != $password2)
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_PASSWORDNOMATCH'), 'error');
			$this->redirect(ADMINTOOLSWP_URL . '&view=AdminPassword');
		}

		$this->sendTroubelshootingEmail('ADMINPASSWORD');

		/** @var \Akeeba\AdminTools\Admin\Model\AdminPassword $model */
		$model = $this->getModel();

		$model->username        = $username;
		$model->password        = $password;
		$model->resetErrorPages = $resetErrorPages;

		$status = $model->protect();

		if ($status)
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_APPLIED'));
			$this->redirect(ADMINTOOLSWP_URL);
		}

		$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOTAPPLIED'), 'error');
		$this->redirect(ADMINTOOLSWP_URL);
	}

	public function unprotect()
	{
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\AdminPassword $model */
		$model  = $this->getModel();
		$status = $model->unprotect();

		if ($status)
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_UNAPPLIED'));
			$this->redirect(ADMINTOOLSWP_URL);
		}

		$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_ERR_ADMINPASSWORD_NOTUNAPPLIED'), 'error');
		$this->redirect(ADMINTOOLSWP_URL);
	}
}
