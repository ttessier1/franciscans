<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\ControlPanel;

use Akeeba\AdminTools\Admin\Helper\Coloriser;
use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Admin\Helper\Session;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\ControlPanel;
use Akeeba\AdminTools\Admin\Model\MasterPassword;
use Akeeba\AdminTools\Admin\Model\Stats;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var  bool      Do I have to ask the user to provide a Download ID? */
	public $needsdlid = false;

	/** @var  string    HTML of the processed CHANGELOG to display in the Changelog modal */
	public $changeLog = '';

	/** @var  bool      Is this a pro version? */
	public $isPro;

	/** @var  bool      Should I display the security exceptions graphs? */
	public $showstats;

	/** @var  string    Visitor IP */
	public $myIP;

	/** @var  string    The error string for the front-end secret word strength issue, blank if there is no problem */
	public $frontEndSecretWordIssue;

	/** @var  string    Proposed new secret word for the front-end file scanner feature */
	public $newSecretWord;

	/** @var  bool      Is this version of Admin Tools too old?	*/
	public $oldVersion;

	/** @var  bool      Do we have a valid password? */
	public $hasValidPassword;

	/** @var  bool      Are the Database Tools features available */
	public $enable_dbtools;

	/** @var  bool		Is the Fix Permissions feature available */
	public $enable_fixperms;

	/** @var  int       Is the .htaccess Maker feature supported on this server? 0 No, 1 Yes, 2 Maybe */
	public $htMakerSupported;

	/** @var  int       Is the web.config Maker feature supported on this server? 0 No, 1 Yes, 2 Maybe */
	public $webConfMakerSupported;

	/** @var  bool      Do we need to run Quick Setup (i.e. not configured yet)? */
	public $needsQuickSetup = false;

	public $statsIframe;

	public function onBeforeDisplay()
	{
		$params = Params::getInstance();

		$this->isPro     = ADMINTOOLSWP_PRO == 1;
		$this->showstats = $params->getValue('showstats', 1);

		/** @var ControlPanel $controlPanelModel */
		$controlPanelModel = $this->getModel();
		/** @var MasterPassword $masterPasswordModel */
		$masterPasswordModel = $this->getModel('MasterPassword');

		$this->changeLog         = Coloriser::colorise(ADMINTOOLSWP_PATH . '/CHANGELOG.php');

		$this->hasValidPassword      = $masterPasswordModel->hasValidPassword();
		$this->enable_dbtools        = $masterPasswordModel->accessAllowed('DatabaseTools');
		$this->enable_fixperms       = $masterPasswordModel->accessAllowed('FixPermissions');

		$this->htMakerSupported      = ServerTechnology::isHtaccessSupported();
		$this->webConfMakerSupported = ServerTechnology::isWebConfigSupported();
		$this->needsdlid             = $controlPanelModel->needsDownloadID();
		$this->needsQuickSetup       = $controlPanelModel->needsQuickSetupWizard();
		$this->myIP                  = $controlPanelModel->getVisitorIP();

		// Pro version setup
		if (defined('ADMINTOOLSWP_PRO') && ADMINTOOLSWP_PRO)
		{
			$this->frontEndSecretWordIssue = $controlPanelModel->getFrontendSecretWordError();
			$this->newSecretWord           = Session::get('newSecretWord', null);
		}

		// Pro version, control panel graphs (only if we enabled them in config options)
		if (defined('ADMINTOOLSWP_PRO') && ADMINTOOLSWP_PRO && $this->showstats)
		{
			// Load JavaScript
			Wordpress::enqueueScript('https://cdn.jsdelivr.net/npm/chart.js@3.2.1/dist/chart.min.js');
			Wordpress::enqueueScript('https://cdn.jsdelivr.net/npm/moment@2.27.0');
			Wordpress::enqueueScript('https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@0.1.1', [
				'chart.min.js',
				'moment@2.27.0'
			]);
			Wordpress::enqueueScript('cpanelgraphs.js', array('jquery-ui-datepicker'));
		}

		Wordpress::enqueueScript('modal.js');
		Wordpress::enqueueScript('cpanel.js');

		$statsModel = new Stats($this->input);
		$this->statsIframe = $statsModel->collectStatistics(true);
	}
}
