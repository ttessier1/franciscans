<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Model\BlacklistedAddresses;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

defined('ADMINTOOLSINC') or die;

class SecurityExceptions extends Controller
{
	public function getByDate()
	{
		parent::display();
	}

	public function getByType()
	{
		parent::display();
	}

	public function ban()
	{
		$this->csrfProtection();

		$id = $this->input->getString('id', '');

		if (empty($id))
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_ERR_SECURITYEXCEPTION_BAN_NOID'), 'error');
			$this->redirect(ADMINTOOLSWP_URL.'&view=SecurityExceptions');
		}

		/** @var \Akeeba\AdminTools\Admin\Model\SecurityExceptions $model */
		$model = $this->getModel();
		$item = $model->getItem($id);

		/** @var BlacklistedAddresses $banModel */
		$banModel = new BlacklistedAddresses($this->input);
		$data     = array(
			'id'          => 0,
			'ip'          => $item->ip,
			'description' => Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_' . strtoupper($item->reason))
		);

		$banModel->save($data);

		$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_BLACKLISTEDADDRESS_SAVED'));
		$this->redirect(ADMINTOOLSWP_URL.'&view=SecurityExceptions');
	}

	public function unban()
	{
		$this->csrfProtection();

		$id = $this->input->getString('id', '');

		if (empty($id))
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_ERR_SECURITYEXCEPTION_BAN_NOID'), 'error');
			$this->redirect(ADMINTOOLSWP_URL.'&view=SecurityExceptions');
		}

		/** @var \Akeeba\AdminTools\Admin\Model\SecurityExceptions $model */
		$model = $this->getModel();
		$item = $model->getItem($id);

		// Let's build a fake input so we can filter the model
		$input = new Input(array(
			'ip' => $item->ip
		));

		/** @var BlacklistedAddresses $banModel */
		$banModel = new BlacklistedAddresses($input);

		$items = $banModel->getItems();

		foreach ($items as $banItem)
		{
			$banModel->delete($banItem->id);
		}

		$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_BLACKLISTEDADDRESS_DELETED'));
		$this->redirect(ADMINTOOLSWP_URL.'&view=SecurityExceptions');
	}
}
