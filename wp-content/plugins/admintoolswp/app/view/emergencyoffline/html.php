<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\EmergencyOffline;

use Akeeba\AdminTools\Admin\Model\EmergencyOffline;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var    bool    Is the site currently offline? */
	public $offline;

	/** @var  string    Htaccess contents */
	public $htaccess;

	public function onBeforeDisplay()
	{
		/** @var EmergencyOffline $model */
		$model = $this->getModel();

		$this->offline  = $model->isOffline();
		$this->htaccess = $model->getHtaccess();
	}
}
