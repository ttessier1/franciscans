<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\SecurityExceptions;

use Akeeba\AdminTools\Admin\Model\SecurityExceptions;

defined('ADMINTOOLSINC') or die;

class Json extends \Akeeba\AdminTools\Library\Mvc\View\Json
{
	protected function onBeforeGetByDate()
	{
		parent::onBeforeDisplay();

		/** @var SecurityExceptions $model */
		$model = $this->getModel();

		$fromDate = $this->input->getString('datefrom', '');
		$toDate   = $this->input->getString('dateto', '');

		$this->items = $model->getExceptionsByDate($fromDate, $toDate);
	}

	protected function onBeforeGetByType()
	{
		parent::onBeforeDisplay();

		/** @var SecurityExceptions $model */
		$model = $this->getModel();

		$fromDate = $this->input->getString('datefrom', '');
		$toDate   = $this->input->getString('dateto', '');

		$this->items = $model->getExceptionsByType($fromDate, $toDate);
	}
}
