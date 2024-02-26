<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

use Akeeba\AdminTools\Admin\Helper\Session;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

defined('ADMINTOOLSINC') or die;

class Databasetools extends Controller
{
	public function display()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\DatabaseTools $model */
		$model = $this->getModel();
		$from  = $this->input->getString('from', null);

		$tables    = (array)$model->findTables();
		$lastTable = $model->repairAndOptimise($from);

		if (empty($lastTable))
		{
			$percent = 100;
		}
		else
		{
			$lastTableID = array_search($lastTable, $tables);
			$percent = round(100 * ($lastTableID + 1) / count($tables));

			if ($percent < 1)
			{
				$percent = 1;
			}

			if ($percent > 100)
			{
				$percent = 100;
			}
		}

		Session::set('lasttable', $lastTable);
		Session::set('percent', $percent);

		parent::display();
	}
}
