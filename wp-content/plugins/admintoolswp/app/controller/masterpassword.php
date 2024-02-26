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

class MasterPassword extends Controller
{
	public function save()
	{
		// CSRF prevention
		$this->csrfProtection();

		$masterpw = $this->input->get('masterpw', '', 'raw');
		$views    = $this->input->get('views', array(), 'raw');

		$restrictedViews = array();

		foreach ($views as $view => $locked)
		{
			if ($locked == 1)
			{
				$restrictedViews[] = $view;
			}
		}

		/** @var \Akeeba\AdminTools\Admin\Model\MasterPassword $model */
		$model = $this->getModel();
		$model->saveSettings($masterpw, $restrictedViews);

		$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_SAVED'));

		$this->redirect(ADMINTOOLSWP_URL);
	}
}
