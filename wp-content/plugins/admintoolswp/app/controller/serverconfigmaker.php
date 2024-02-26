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

class ServerConfigMaker extends Controller
{
	use SendTroubleshootingEmail;

	/**
	 * The prefix for the language strings of the information and error messages
	 *
	 * @var string
	 */
	protected $langKeyPrefix = 'COM_ADMINTOOLS_LBL_HTACCESSMAKER_';

	public function preview()
	{
		parent::display();
	}

	public function save()
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\ServerConfigMaker $model */
		$model = $this->getModel();

		$data = $this->input->getData();
		$model->saveConfiguration($data);

		$this->getView()->enqueueMessage(Language::_($this->langKeyPrefix . 'SAVED'));

		$this->redirect(ADMINTOOLSWP_URL . '&view=' . $this->name);
	}

	public function apply()
	{
		$this->sendTroubelshootingEmail('ServerConfigMaker');

		/** @var \Akeeba\AdminTools\Admin\Model\ServerConfigMaker $model */
		$model = $this->getModel();

		$data = $this->input->getData();
		$model->saveConfiguration($data);
		$status = $model->writeConfigFile();

		if (!$status)
		{
			$this->getView()->enqueueMessage(Language::_($this->langKeyPrefix . 'NOTAPPLIED'), 'error');
			$this->redirect(ADMINTOOLSWP_URL . '&view=' . $this->name);
		}

		$this->getView()->enqueueMessage(Language::_($this->langKeyPrefix . 'APPLIED'));
		$this->redirect(ADMINTOOLSWP_URL . '&view=' . $this->name);
	}
}
