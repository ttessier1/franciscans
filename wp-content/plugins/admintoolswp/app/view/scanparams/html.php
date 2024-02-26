<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\ScanParams;

use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\Params;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var  array Plugin configuration */
	public $config;

	protected function onBeforeDisplay()
	{
		/** @var Params $model */
		$model = $this->getModel('Params');
		$this->config = $model->getItems();
	}
}
