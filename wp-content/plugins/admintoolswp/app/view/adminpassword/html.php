<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\AdminPassword;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Model\AdminPassword;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/**
	 * .htaccess username
	 *
	 * @var  string
	 */
	public $username;

	/**
	 * .htaccess password
	 *
	 * @var  string
	 */
	public $password;

	/**
	 * Is the backend locked?
	 *
	 * @var  string
	 */
	public $adminLocked;

	/**
	 * Should I reset custom error pages?
	 *
	 * @var   bool
	 *
	 * @since 1.0.5
	 */
	public $resetErrorPages;

	protected function onBeforeDisplay()
	{
		/** @var AdminPassword $model */
		$model = $this->getModel();

		$this->username        = $this->input->get('username', '', 'raw');
		$this->password        = $this->input->get('password', '', 'raw');
		$this->resetErrorPages = $this->input->get('resetErrorPages', 1, 'int');
		$this->adminLocked     = $model->isLocked();
	}
}
