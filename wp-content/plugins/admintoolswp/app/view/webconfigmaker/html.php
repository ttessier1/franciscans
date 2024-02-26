<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\WebConfigMaker;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Admin\Model\HtaccessMaker;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/**
	 * web.config contents for preview
	 *
	 * @var  string
	 */
	public $webConfig;

	/**
	 * The web.config Maker configuration
	 *
	 * @var  array
	 */
	public $wcconfig;

	/** @var    int     Is this supported? 0 No, 1 Yes, 2 Maybe */
	public $isSupported;

	protected function onBeforePreview()
	{
		/** @var HtaccessMaker $model */
		$model           = $this->getModel();
		$this->webConfig = $model->makeConfigFile();
		$this->setLayout('plain');
	}

	protected function onBeforeDisplay()
	{
		/** @var HtaccessMaker $model */
		$model             = $this->getModel();
		$this->wcconfig    = $model->loadConfiguration();
		$this->isSupported = ServerTechnology::isHtaccessSupported();
	}
}
