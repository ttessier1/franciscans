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

class Salts extends Controller
{
	public function change()
	{
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\Salts $model */
		$model = $this->getModel();

		$model->changeSalts();

		$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_SALTS_SALTS_CHANGED'));

		$this->redirect(ADMINTOOLSWP_URL);
	}
}
