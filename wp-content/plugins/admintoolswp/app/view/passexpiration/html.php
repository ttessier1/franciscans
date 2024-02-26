<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\PassExpiration;

use Akeeba\AdminTools\Admin\Model\ConfigureWAF;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var array WAF Configuration */
	public $wafconfig = array();

	protected function onBeforeDisplay()
	{
		/** @var ConfigureWAF $model */
		$model = $this->getModel('ConfigureWAF');
		$this->wafconfig = $model->getItems();
	}
}
