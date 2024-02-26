<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

use Akeeba\AdminTools\Admin\View\FixPermissions\Html;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

defined('ADMINTOOLSINC') or die;

class FixPermissions extends Controller
{
	public function display()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\FixPermissions $model */
		$model = $this->getModel();
		$state = $model->startScanning();

		/** @var Html $view */
		$view = $this->getView();
		$view->setModel('FixPermissions', $model);
		$view->scanstate = $state;

		parent::display();
	}

	public function run()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\FixPermissions $model */
		$model = $this->getModel();
		$state = $model->run();

		/** @var Html $view */
		$view = $this->getView();
		$view->setModel('FixPermissions', $model);
		$view->scanstate = $state;

		parent::display();
	}
}
