<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\WAFEmailTemplates;

use Akeeba\AdminTools\Admin\Model\WAFEmailTemplates;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	protected function onBeforeDisplay()
	{
		/** @var WAFEmailTemplates $model */
		$model = $this->getModel();

		$this->items = $model->getItems();
		$this->total = $model->getTotal();
		$this->limitstart = $this->input->getInt('limitstart', 0);
	}

	protected function onBeforeEdit()
	{
		/** @var WAFEmailTemplates $model */
		$model = $this->getModel();
		$id    = $this->input->getInt('admintools_waftemplate_id', 0);

		$this->item = $model->getItem($id);
	}
}
