<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

defined('ADMINTOOLSINC') or die;

class OptimizeWaf extends Controller
{
	public function create()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\OptimizeWaf $model */
		$model = $this->getModel();
		$env   = $this->input->getString('environment', '');

		$msg = Language::_('COM_ADMINTOOLS_OPTIMIZEWAF_AUTOPREP_ENABLED');
		$type = 'info';

		try
		{
			$model->enableAutoPrepend($env);
		}
		catch (\Exception $e)
		{
			$msg = $e->getMessage();
			$type = 'error';
		}

		$this->getView()->enqueueMessage($msg, $type);
		$this->redirect(ADMINTOOLSWP_URL.'&view=OptimizeWAF');
	}

	public function disable()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\OptimizeWaf $model */
		$model = $this->getModel();

		$msg = Language::_('COM_ADMINTOOLS_OPTIMIZEWAF_AUTOPREP_DISABLED');
		$type = 'info';

		try
		{
			$model->disableAutoPrepend();
		}
		catch (\Exception $e)
		{
			$msg = $e->getMessage();
			$type = 'error';
		}

		$this->getView()->enqueueMessage($msg, $type);
		$this->redirect(ADMINTOOLSWP_URL.'&view=OptimizeWAF');
	}
}
