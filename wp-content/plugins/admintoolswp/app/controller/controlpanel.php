<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

use AdminToolsInstaller;
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Session;
use Akeeba\AdminTools\Admin\Model\Update;
use Akeeba\AdminTools\Library\Encrypt\Randval;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

defined('ADMINTOOLSINC') or die;

class ControlPanel extends Controller
{
	public function display()
	{
		// Try to load and fix the database
		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		$model->checkAndFixDatabase();

		$updated = false;

		// Check if the MU plugin is up to date
		$mu_folder = ABSPATH.'wp-content/mu-plugins';

		if (defined('WPMU_PLUGIN_DIR') && WPMU_PLUGIN_DIR)
		{
			$mu_folder = WPMU_PLUGIN_DIR;
		}

		if (!AdminToolsInstaller::fileUpToDate($mu_folder.'/admintoolswp.php', 'ADMINTOOLSWP_MUPLUGIN_VERSION'))
		{
			AdminToolsInstaller::installPlugin();
			$updated = true;
		}

		// Check if the WAF file is up to date
		if (!AdminToolsInstaller::fileUpToDate(ABSPATH.'admintools-waf.php', 'ADMINTOOLSWP_AUTOPREPEND_VERSION'))
		{
			AdminToolsInstaller::updateAutoPrependFile();
			$updated = true;
		}

		// If I changed anything, let's invalid the OP cache
		if ($updated)
		{
			AdminToolsInstaller::clearOpcodeCaches();
		}

		// Delete the old log files if logging is disabled
		$model->deleteOldLogs();

		parent::display();
	}

	public function login()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\MasterPassword $model */
		$model = $this->getModel('MasterPassword');
		$password = $this->input->get('userpw', '', 'raw');
		$model->setUserPassword($password);

		$this->redirect(ADMINTOOLSWP_URL);
	}

	public function reloadUpdateInformation()
	{
		$msg = null;

		$updateModel = new Update($this->input);
		$updateModel->getUpdateInformation(true);

		$view = $this->getView();
		$msg  = Language::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_UPDATE_INFORMATION_RELOADED');
		$view->enqueueMessage($msg, 'info');

		$this->redirect(ADMINTOOLSWP_URL);
	}

	public function updateinfo()
	{
		@ob_clean();

		$force = $this->input->get('force', false, 'bool');

		$updateModel = new Update($this->input);
		$updateModel->getUpdateInformation($force);

		$result = '';

		if ($updateModel->hasUpdate())
		{
			$updateInfo = (object) $updateModel->getUpdateInformation();
			$strings    = [
				'header'  => Language::sprintf('COM_ADMINTOOLS_MSG_CONTROLPANEL_UPDATEFOUND', $updateInfo->get('version')),
				'button'  => Language::sprintf('COM_ADMINTOOLS_MSG_CONTROLPANEL_UPDATENOW', $updateInfo->get('version')),
				'infourl' => $updateInfo->get('infourl'),
				'infolbl' => Language::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_MOREINFO'),
			];

			$url = get_admin_url(null, 'update-core.php');

			$result = <<<ENDRESULT
	<div class="akeeba-block--warning">
		<h3>
			{$strings['header']}
		</h3>
		<p>
			<a href="{$url}" class="akeeba-btn--primary">
				{$strings['button']}
			</a>
			<a href="{$strings['infourl']}" target="_blank" class="akeeba-btn--ghost">
				{$strings['infolbl']}
			</a>
		</p>
	</div>
ENDRESULT;
		}

		echo '###' . $result . '###';

		die();
	}

	public function selfblocked()
	{
		$externalIP = $this->input->getString('ip', '');

		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		$result = (int)$model->isMyIPBlocked($externalIP);

		ob_clean();

		echo '###' . $result . '###';

		die();
	}

	public function unblockme()
	{
		$externalIP = $this->input->getString('ip', '');

		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();

		$model->unblockMyIP($externalIP);

		$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_CONTROLPANEL_IP_UNBLOCKED'));

		$this->redirect(ADMINTOOLSWP_URL);
	}

	/**
	 * Applies the Download ID when the user is prompted about it in the Control Panel
	 */
	public function applydlid()
	{
		$this->csrfProtection();

		$msg     = Language::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_INVALIDDOWNLOADID');
		$msgType = 'error';
		$dlid    = $this->input->getString('dlid', '');

		// If the Download ID seems legit let's apply it
		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			$msg     = null;
			$msgType = null;

			$params = Params::getInstance();

			$params->setValue('downloadid', $dlid, true);
		}

		if ($msg)
		{
			$this->getView()->enqueueMessage($msg, $msgType);
		}

		$this->redirect(ADMINTOOLSWP_URL);
	}

	/**
	 * Enables the IP workarounds option or disables the warning
	 */
	public function IpWorkarounds()
	{
		$enable = $this->input->getInt('enable', 0);
		$msg    = null;

		if ($enable)
		{
			$msg = Language::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_ENABLED');
		}

		/** @var \Akeeba\AdminTools\Admin\Model\ControlPanel $model */
		$model = $this->getModel();
		$model->setIpWorkarounds($enable);

		if ($customURL = $this->input->getBase64('returnurl', ''))
		{
			$customURL = base64_decode($customURL);
		}

		$returnUrl = $customURL ? $customURL : ADMINTOOLSWP_URL;

		if ($msg)
		{
			$this->getView()->enqueueMessage($msg, 'info');
		}

		$this->redirect($returnUrl);
	}

	/**
	 * Reset the Secret Word for front-end and remote backup
	 *
	 * @return  void
	 */
	public function resetSecretWord()
	{
		$this->csrfProtection();

		$newSecret = Session::get('newSecretWord', null);

		if (empty($newSecret))
		{
			$random    = new Randval();
			$newSecret = $random->generateString(32);
			Session::set('newSecretWord', $newSecret);
		}

		$params = Params::getInstance();
		$params->setValue('frontend_secret_word', $newSecret);
		$params->save();

		$msg = Language::sprintf('COM_ADMINTOOLS_MSG_CONTROLPANEL_FESECRETWORD_RESET', $newSecret);

		$this->getView()->enqueueMessage($msg);

		$this->redirect(ADMINTOOLSWP_URL);
	}
}
