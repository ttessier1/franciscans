<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\Redirections;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Model\Redirections;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/**
	 * Is the URL Redirection feature enabled?
	 *
	 * @var  bool
	 */
	public $urlredirection;

	protected function onBeforeDisplay()
	{
		/** @var Redirections $model */
		$model                = $this->getModel();
		$urlredirection       = $model->getRedirectionState();
		$this->urlredirection = $urlredirection;

		$this->items      = $model->getItems();
		$this->total      = $model->getTotal();
		$this->limitstart = $this->input->getInt('limitstart', 0);
	}

	protected function onBeforeEdit()
	{
		/** @var Redirections $model */
		$model = $this->getModel();
		$id    = $this->input->getInt('id', 0);

		$this->item = $model->getItem($id);
	}
}
