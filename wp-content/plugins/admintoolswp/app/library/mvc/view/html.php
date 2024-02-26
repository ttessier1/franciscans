<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Library\Mvc\View;

use Akeeba\AdminTools\Admin\Helper\Params as PluginParams;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Input\Input;

defined('ADMINTOOLSINC') or die;

class Html
{
	protected $name = '';
	protected $layout = 'default';

	/** @var \stdClass Table record set */
	protected $item = null;

	/** @var array Array containing the record set (used on browse methods) */
	protected $items = [];

	/** @var int Total amount of records in the table */
	protected $total = 0;
	protected $messages = [];

	/** @var string Records the task that is currently executed */
	protected $task = '';

	/** @var int Holds the offset */
	protected $limitstart = 0;

	/** @var Input */
	protected $input;

	/** @var bool    Do we request raw output (ie tmpl=component)? */
	protected $rawoutput = false;

	protected $modelInstances = [];

	/**
	 * Html constructor.
	 *
	 * @param   Input  $input
	 */
	public function __construct($input)
	{
		// Fetch the name from the full namespace
		$classname = get_class($this);
		$parts     = explode('\\', $classname);

		$this->name  = $parts[count($parts) - 2];
		$this->input = $input;

		if ($this->input->getCmd('tmpl', '') == 'component')
		{
			$this->rawoutput = true;
		}
	}

	/**
	 * If there are any messages enqueued, it will display them
	 */
	public function preRender()
	{
		if ($this->rawoutput)
		{
			// If we have to handle a raw output we have to clean the buffer created so far
			@ob_clean();
		}

		$params   = $params = PluginParams::getInstance();
		$darkMode = $params->getValue('darkmode', -1);
		$suffix   = ($darkMode == 1) ? '--dark' : '';
		$html     = sprintf("<div class=\"akeeba-renderer-fef%s\">", $suffix);

		// Conditional HTML comment for IE9 compatibility
		$html .= '<!--[if IE]>';
		$html .= '<div class="ie9">';
		$html .= '<![endif]-->';

		// Retrieve the list of messages stored for the current user
		$messages = get_user_meta(get_current_user_id(), 'admintoolswp_messages', true);

		// If the meta key is not defined, we get an empty string instead of an empty array
		if (!$messages)
		{
			$messages = [];
		}

		update_user_meta(get_current_user_id(), 'admintoolswp_messages', []);

		foreach ($messages as $message)
		{
			switch ($message['type'])
			{
				case 'error':
					$class = 'akeeba-block--failure';
					break;
				case 'warning':
					$class = 'akeeba-block--warning';
					break;
				default:
					$class = 'akeeba-block--success';
			}

			$html .= '<div class="' . $class . '"><p>' . $message['msg'] . '</p></div>';
		}

		echo $html;
	}

	public function afterRender()
	{
		// Conditional HTML comment for IE9 compatibility
		$html = '<!--[if IE9]>';
		$html .= '</div>';
		$html .= '<![endif]-->';

		// Closing div for Akeeba FEF
		$html .= '</div>';

		echo $html;

		// If a "component" page has been requested, we have to manually create a "mini-page" with the full CSS
		// and Javascript required for styling
		if ($this->rawoutput)
		{
			$contents = @ob_get_clean();
			@ob_end_clean();

			echo $this->createRawPage($contents);

			die();
		}
	}

	public function display()
	{
		$this->preRender();

		$eventName = 'onBefore' . ucfirst($this->task);

		if (method_exists($this, $eventName))
		{
			$this->$eventName();
		}

		$this->includeTemplate();

		$eventName = 'onAfter' . ucfirst($this->task);

		if (method_exists($this, $eventName))
		{
			$this->$eventName();
		}

		$this->afterRender();
	}

	public function setModel($name, $instance)
	{
		$this->modelInstances[$name] = $instance;

		return $this;
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
			$className = '\\Akeeba\\AdminTools\\Admin\\Model\\' . ucfirst($modelName);

			if (!class_exists($className))
			{
				throw new \RuntimeException(sprintf("Model class %s not found", $className));
			}

			$this->modelInstances[$modelName] = new $className($this->input);
		}

		return $this->modelInstances[$modelName];
	}

	public function escape($var)
	{
		return htmlspecialchars($var ?? '', ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Stores a message in the user meta table, so we can display it to the user on the next page load
	 *
	 * @param   string       $msg   Message to display
	 * @param   null|string  $type  Message type. Supported values: error, warning, value
	 *
	 * @return $this
	 */
	public function enqueueMessage($msg, $type = null)
	{
		$messages = get_user_meta(get_current_user_id(), 'admintoolswp_messages', true);

		// If the meta key is not defined, we get an empty string instead of an empty array
		if (!$messages)
		{
			$messages = [];
		}

		$messages[] = ['msg' => $msg, 'type' => $type];

		update_user_meta(get_current_user_id(), 'admintoolswp_messages', $messages);

		return $this;
	}

	public function getTask()
	{
		return $this->task;
	}

	public function setTask($task)
	{
		$this->task = $task;

		return $this;
	}

	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Check if the layout file exists and try to include it
	 *
	 * @return  bool    True if the layout exists, otherwise false
	 */
	protected function includeTemplate()
	{
		$path = ADMINTOOLSWP_PATH . '/app/view/' . strtolower($this->name) . '/tmpl/' . $this->layout . '.php';

		if (file_exists($path))
		{
			include $path;

			return true;
		}

		return false;
	}

	/**
	 * Since we're going to stop the execution after our plugin made its work, we have to manually craft a full HTML
	 * page, adding all the global elements (ie html/head/body) AND load all our scripts on our own
	 *
	 * @param $contents
	 *
	 * @return string
	 */
	private function createRawPage($contents)
	{
		$head = Wordpress::getMediaForPage();

		$html = '<html>';
		$html .= '<head>';
		$html .= "\n" . $head . "\n";
		$html .= '</head>';
		$html .= '<body>';
		$html .= $contents;
		$html .= '</body>';
		$html .= '</html>';

		return $html;
	}
}
