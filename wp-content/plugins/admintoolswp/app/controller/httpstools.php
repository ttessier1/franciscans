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

class HttpsTools extends Controller
{
	/**
	 * Overrides standard save method to call our own saveConfig method inside the model
	 */
	public function save()
	{
		$this->csrfProtection();

		if (is_array($this->input))
		{
			$data = $this->input;
		}
		else
		{
			$data = $this->input->getData();
		}

		/** @var \Akeeba\AdminTools\Admin\Model\HttpsTools $model */
		$model = $this->getModel();
		$model->saveConfig($data);

		$this->getView()
			 ->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_HTTPSTOOLS_CONFIGSAVED'), 'info');

		$this->redirect(ADMINTOOLSWP_URL.'&view='.$this->name);
	}
}
