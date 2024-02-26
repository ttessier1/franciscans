<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\OptimizeWaf;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\ServerInfo;
use Akeeba\AdminTools\Admin\Model\OptimizeWaf;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var bool	Is auto-prepend enabled? */
	public $enabled = false;

	/** @var bool	Is auto-prepend mode correctly configured? */
	public $configured = false;

	/** @var string	Current environment, based on our best guess */
	public $environment;

	protected function onBeforeDisplay()
	{
		/** @var OptimizeWaf $model */
		$model = $this->getModel();

		$this->environment = ServerInfo::getEnvironment();
		$this->enabled	   = $model->autoPrependEnabled();
		$this->configured  = $model->correctlyConfigured();
	}
}
