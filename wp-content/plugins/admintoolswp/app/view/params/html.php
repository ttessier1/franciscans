<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\Params;

use Akeeba\AdminTools\Admin\Model\Params;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var  array Plugin configuration */
	public $config;

	protected function onBeforeDisplay()
	{
		/** @var Params $model */
		$model = $this->getModel();
		$this->config = $model->getItems();
	}
}
