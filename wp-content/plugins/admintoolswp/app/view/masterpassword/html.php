<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\MasterPassword;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/**
	 * Current master password
	 *
	 * @var  string
	 */
	public $masterpw;

	public function onBeforeDisplay()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\MasterPassword $model */
		$model          = $this->getModel();
		$this->masterpw = $model->getMasterPassword();
		$this->items    = $model->getItemList();
	}
}
