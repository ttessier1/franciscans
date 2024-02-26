<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\HttpsTools;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Model\HttpsTools;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/**
	 * The configuration for this feature
	 *
	 * @var  array
	 */
	public $salconfig;

	protected function onBeforeDisplay()
	{
		/** @var HttpsTools $model */
		$model  = $this->getModel();
		$config = $model->getConfig();

		$this->salconfig = $config;
	}
}
