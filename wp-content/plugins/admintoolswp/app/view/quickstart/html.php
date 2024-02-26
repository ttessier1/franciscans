<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\QuickStart;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\ControlPanel;
use Akeeba\AdminTools\Admin\Model\QuickStart;
use Akeeba\AdminTools\Admin\Model\ConfigureWAF;
use Akeeba\AdminTools\Library\Encrypt\Randval;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var  string    The detected IP of the current visitor */
	public $myIp = '';

	/** @var  array     The configuration of WAF */
	public $wafconfig = null;

	/** @var  bool      Is this the first run of the Quick Setup wizard, i.e. no existing configuration was detected?
	 */
	public $isFirstRun = true;

	/** @var  string    Username for the Password Protect Administrator Directory feature */
	public $admin_username;

	/** @var  string    Password for the Password Protect Administrator Directory feature */
	public $admin_password;

	/** @var  bool   Does the server technology seem to support .htaccess files? */
	public $hasHtaccess = false;

	/** @var  bool      Is this a pro version? */
	public $isPro       = false;

	protected function onBeforeDisplay()
	{
		$this->isPro = ADMINTOOLSWP_PRO == 1;

		// Get the reported IP
		/** @var ControlPanel $cpanelModel */
		$cpanelModel = new ControlPanel($this->input);
		$this->myIp  = $cpanelModel->getVisitorIP();

		// Get the WAF configuration
		/** @var ConfigureWAF $wafConfigModel */
		$wafConfigModel  = $this->getModel('ConfigureWAF');
		$this->wafconfig = $wafConfigModel->getItems();

		$user = get_user_by('id', get_current_user_id());

		// Populate email addresses if necessary
		if (empty($this->wafconfig['emailonadminlogin']))
		{
			$this->wafconfig['emailonadminlogin'] = $user->user_email;
		}

		// Get the administrator username/password
		$this->admin_username = '';
		$this->admin_password = '';

		/** @var QuickStart $model */
		$model = $this->getModel();
		$this->isFirstRun = $model->isFirstRun();

		$this->hasHtaccess = ServerTechnology::isHtaccessSupported();

		Wordpress::enqueueScript('quickstart.js');
	}
}
