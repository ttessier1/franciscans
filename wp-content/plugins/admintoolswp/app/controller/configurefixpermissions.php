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

class ConfigureFixPermissions extends Controller
{
	public function savedefaults()
	{
		// CSRF prevention
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\ConfigureFixPermissions $model */
		$model = $this->getModel();
		$model->saveDefaults();

		$message = Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFAULTSSAVED');
		$this->getView()->enqueueMessage($message);
		$this->redirect(ADMINTOOLSWP_URL.'&view=ConfigureFixPermissions');
	}

	/**
	 * Saves the custom permissions and reloads the current view
	 */
	public function saveperms()
	{
		// CSRF prevention
		$this->csrfProtection();

		$this->save_custom_permissions();

		$message = Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_CUSTOMSAVED');
		$this->getView()->enqueueMessage($message);

		$path = $this->input->get('path', '', 'raw');
		$this->redirect(ADMINTOOLSWP_URL.'&view=ConfigureFixPermissions&path=' . urlencode($path));
	}

	/**
	 * Saves the custom permissions, applies them and reloads the current view
	 */
	public function saveapplyperms()
	{
		// CSRF prevention
		$this->csrfProtection();

		$this->save_custom_permissions(true);

		$message = Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_CUSTOMSAVEDAPPLIED');
		$this->getView()->enqueueMessage($message);

		$path = $this->input->get('path', '', 'raw');
		$this->redirect(ADMINTOOLSWP_URL.'&view=ConfigureFixPermissions&path=' . urlencode($path));
	}

	private function save_custom_permissions($apply = false)
	{
		/** @var \Akeeba\AdminTools\Admin\Model\ConfigureFixPermissions $model */
		$model = $this->getModel();
		$model->applyPath();

		$model->savePermissions($apply);
	}
}
