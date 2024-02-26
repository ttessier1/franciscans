<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Dispatcher;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Params as PluginParams;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\MasterPassword;
use Akeeba\AdminTools\Library\Inflector\Inflector;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

defined('ADMINTOOLSINC') or die;

class Dispatcher
{
	public static function route()
	{
		global $_REAL_REQUEST;

		$page = \AdminToolsWP::$dirName . '/admintoolswp.php';

		// WordPress is always escaping the input. WTF!
		// See http://stackoverflow.com/questions/8949768/with-magic-quotes-disabled-why-does-php-wordpress-continue-to-auto-escape-my
		if (isset($_REAL_REQUEST))
		{
			$input = new Input($_REAL_REQUEST, ['magicQuotesWorkaround' => true]);
		}
		else
		{
			$fakeRequest = array_map('stripslashes_deep', $_REQUEST);
			$input       = new Input($fakeRequest, ['magicQuotesWorkaround' => true]);
		}

		// Is this an Admin Tools page?
		if ($input->getPath('page', '') != $page)
		{
			return;
		}

		self::convertLimitStart($input);

		$forced    = false;
		$inflector = new Inflector();

		$view = $input->get('view', 'ControlPanel');
		$task = $input->get('task', 'display');

		// Check if the current user has a valid password for the page he's trying to visit
		/** @var MasterPassword $model */
		$model      = new MasterPassword($input);
		$view_check = $inflector->singularize($view);

		if (!$model->accessAllowed($view_check))
		{
			$view   = 'ControlPanel';
			$task   = 'display';
			$forced = true;
		}

		$classname = 'Akeeba\\AdminTools\\Admin\\Controller\\' . ucfirst($view);

		// Force ControlPanel view if we're trying to use a non-existing class
		if (!class_exists($classname))
		{
			$classname = 'Akeeba\\AdminTools\\Admin\\Controller\\ControlPanel';
		}

		/** @var Controller $controller */
		$controller = new $classname($input);

		if (!method_exists($controller, $task))
		{
			$task = 'display';
		}

		$controller->setTask($task);

		// If the check vs the master password failed, let's inject an error message for the user
		if ($forced)
		{
			$controller->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_NOTAUTHORIZED'), 'error');
		}

		$params   = $params = PluginParams::getInstance();
		$darkMode = $params->getValue('darkmode', -1);

		// Add some default media files
		Wordpress::enqueueScript('system.js');
		Wordpress::enqueueStyle('fef/css/style.css');
		Wordpress::enqueueStyle('css/backend.min.css');

		if ($darkMode != 0)
		{
			Wordpress::enqueueStyle('fef/css/dark.css');
			Wordpress::enqueueStyle('css/dark.min.css');
		}

		$controller->$task();

		// Finally add everything to WordPress using its own API
		Wordpress::addMediaToWordPress();
	}

	/**
	 * In Wordpress you can navigate using the links or directly type the page, this function
	 * takes care of converting the "page" value into a "limitstart" one.
	 *
	 * @param   Input  $input
	 */
	private static function convertLimitStart(&$input)
	{
		$paged = $input->getInt('paged', 0);
		$paged -= 1;

		// First page or invalid value, let's use limitstart directly
		if ($paged <= 0)
		{
			return;
		}

		$limit     = Wordpress::get_page_limit();
		$new_start = $paged * $limit;

		$data = $input->getData();
		unset($data['paged']);

		$input->set('limitstart', $new_start);
	}
}
