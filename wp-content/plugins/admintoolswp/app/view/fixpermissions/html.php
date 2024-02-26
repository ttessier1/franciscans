<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\FixPermissions;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\FixPermissions;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/**
	 * Do we need to perform more steps?
	 *
	 * @var  bool
	 */
	public $more;

	/** @var bool Scan state, passed from the model */
	public $scanstate = false;

	/**
	 * Percent complete
	 *
	 * @var  int
	 */
	public $percentage;

	protected function onBeforeDisplay()
	{
		Wordpress::enqueueScript('fixpermissions.js');

		/** @var FixPermissions $model */
		$model = $this->getModel();
		$state = $this->scanstate;

		$total = $model->totalFolders;
		$done  = $model->doneFolders;

		$percent = 100;
		$more    = false;

		if ($state)
		{
			if ($total > 0)
			{
				$percent = min(max(round(100 * $done / $total), 1), 100);
			}

			$more = true;
		}

		$this->more       = $more;
		$this->percentage = $percent;
	}

	protected function onBeforeRun()
	{
		$this->onBeforeDisplay();
	}
}
