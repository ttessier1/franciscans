<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Library\Mvc\Controller;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\AdminTools\Library\Mvc\View\Html;

defined('ADMINTOOLSINC') or die;

abstract class Controller
{
	protected $name = '';
	protected $modelInstances = array();
	protected $viewInstances = array();

	/** @var string Records the task that is currently executed */
	protected $task = '';

	/** @var Input  */
	protected $input;

	/**
	 * @param   Input   $input
	 */
	public function __construct($input)
	{
		// Fetch the name from the full namespace
		$classname = get_class($this);
		$parts     = explode('\\', $classname);

		$this->name  = $parts[count($parts) - 1];

		$this->input = $input;
	}

	public function setTask($task)
	{
		$this->task = $task;
	}

	public function add()
	{
		if (method_exists($this, 'onBeforeAdd'))
		{
			$this->onBeforeAdd();
		}

		/** @var Html $view */
		$view = $this->getView();
		$view->setLayout('form')->setTask($this->task);

		$this->display();
	}

	public function edit()
	{
		if (method_exists($this, 'onBeforeEdit'))
		{
			$this->onBeforeEdit();
		}

		$view = $this->getView();
		$view->setLayout('form')->setTask($this->task);

		$this->display();
	}

	public function save()
	{
		if (method_exists($this, 'onBeforeSave'))
		{
			$this->onBeforeSave();
		}

		$this->csrfProtection();

		$msg  = Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE_OK');
		$type = 'info';

		/** @var Model $model */
		$model = $this->getModel();

		try
		{
			$model->save();
			$success = true;
		}
		catch (\RuntimeException $exception)
		{
			$msg  = $exception->getMessage();
			$type = 'error';
			$success = false;
		}

		$this->getView()->enqueueMessage($msg, $type)->setTask('display');

		if (method_exists($this, 'onAfterSave'))
		{
			$this->onAfterSave($success);
		}

		wp_redirect(ADMINTOOLSWP_URL.'&view='.$this->name);
	}

	public function delete()
	{
		if (method_exists($this, 'onBeforeDelete'))
		{
			$this->onBeforeDelete();
		}

		$this->csrfProtection();

		$msg  = Language::_('COM_ADMINTOOLS_LBL_COMMON_DELETE_OK');
		$type = 'info';

		/** @var Model $model */
		$model = $this->getModel();

		if (!$model->delete())
		{
			$msg  = Language::_('COM_ADMINTOOLS_LBL_COMMON_DELETE_ERR');
			$type = 'error';
		}

		$this->getView()->enqueueMessage($msg, $type)->setTask('display');

		wp_redirect(ADMINTOOLSWP_URL.'&view='.$this->name);
	}

	public function display()
	{
		if (method_exists($this, 'onBeforeDisplay'))
		{
			$this->onBeforeDisplay();
		}

		/** @var Html $view */
		$view = $this->getView();

		if (!$view->getTask())
		{
			$view->setTask($this->task);
		}

		$view->display();
	}

	public function getModel($name = null)
	{
		$modelName = $this->name;

		if (!empty($name))
		{
			$modelName = $name;
		}

		if (!array_key_exists($modelName, $this->modelInstances))
		{
			$className = '\\Akeeba\\AdminTools\\Admin\\Model\\'.ucfirst($modelName);

			if (!class_exists($className))
			{
				throw new \RuntimeException(sprintf("Model class %s not found", $className));
			}

			$this->modelInstances[$modelName] = new $className($this->input);
		}

		return $this->modelInstances[$modelName];
	}

	/**
	 * @param null $name
	 *
	 * @return Html
	 */
	public function getView($name = null)
	{
		$viewName = $this->name;

		if (!empty($name))
		{
			$viewName = $name;
		}

		if (!array_key_exists($viewName, $this->viewInstances))
		{
			$format = strtolower($this->input->get('format', 'html'));

			$className = 'Akeeba\\AdminTools\\Admin\\View\\'.ucfirst($this->name).'\\'.ucfirst($format);

			if (!class_exists($className))
			{
				throw new \RuntimeException(sprintf("View class %s not found", $className));
			}

			$this->viewInstances[$viewName] = new $className($this->input);
		}

		return $this->viewInstances[$viewName];
	}

	/**
	 * Issues a redirect to a specific URL and stops the execution of the application
	 *
	 * @param $url
	 */
	public function redirect($url)
	{
		wp_redirect($url);
		die();
	}

	protected function csrfProtection()
	{
		$token = $this->input->get('_wpnonce');

		if (!wp_verify_nonce($token, 'post'.$this->name) && !wp_verify_nonce($token, 'get'.$this->name))
		{
			die("Invalid security token!");
		}
	}
}
