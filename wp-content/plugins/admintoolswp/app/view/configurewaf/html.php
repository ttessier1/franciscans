<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\ConfigureWAF;

use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\ConfigureWAF;
use Akeeba\AdminTools\Admin\Model\ControlPanel;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var array WAF Configuration */
	public $wafconfig = array();

	/** @var string The detected visitor's IP address */
	public $myIP = '';

	public $longConfig = false;

	protected function onBeforeDisplay()
	{
		Wordpress::enqueueScript('configurewaf.js');

		/** @var ConfigureWAF $model */
		$model = $this->getModel();
		$this->wafconfig = $model->getItems();

		/** @var ControlPanel $cpanel */
		$cpanel = $this->getModel('ControlPanel');
		$this->myIP = $cpanel->getVisitorIP();

		$params = Params::getInstance();
		$this->longConfig = $params->getValue('longConfig', 0);

		if (!$this->longConfig)
		{
			Wordpress::enqueueScript('../fef/js/tabs.min.js');
		}
	}
}
