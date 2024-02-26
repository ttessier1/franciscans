<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

class Redirections extends Controller
{
	public function copy()
	{
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\Redirections $model */
		$model = $this->getModel();
		$data = array('published' => 0);

		$url = ADMINTOOLSWP_URL.'&view=Redirections';

		try
		{
			$model->copy($data);

			$this->redirect($url);
		}
		catch (\Exception $e)
		{
			$this->getView()->enqueueMessage($e->getMessage(), 'error');
			$this->redirect($url);
		}
	}

	public function applypreference()
	{
		$newState = $this->input->getInt('urlredirection', 1);

		/** @var \Akeeba\AdminTools\Admin\Model\Redirections $model */
		$model = $this->getModel();
		$model->setRedirectionState($newState);

		$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE_SAVED'));
		$this->redirect(ADMINTOOLSWP_URL.'&view=Redirections');
	}
}
