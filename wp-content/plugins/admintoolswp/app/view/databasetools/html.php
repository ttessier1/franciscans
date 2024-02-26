<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\Databasetools;

use Akeeba\AdminTools\Admin\Helper\Session;
use Akeeba\AdminTools\Admin\Helper\Wordpress;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/**
	 * Table being processed
	 *
	 * @var  string
	 */
	public $table;

	/**
	 * Percent complete
	 *
	 * @var  int
	 */
	public $percent;

	protected function onBeforeDisplay()
	{
		$lastTable     = Session::get('lasttable', '');
		$percent       = Session::get('percent', '');

		$this->table   = $lastTable;
		$this->percent = $percent;

		Wordpress::enqueueScript('databasetools.js');
	}
}
