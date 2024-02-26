<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Library\Mvc\View;

defined('ADMINTOOLSINC') or die;

class Json extends Html
{
	protected $layout = 'json';

	/**
	 * Nothing to render before displaying the main content
	 */
	public function preRender()
	{
		return;
	}

	/**
	 * Nothing to render after displaying the main content
	 */
	public function afterRender()
	{
		return;
	}

	/**
	 * Clean up the buffer before outputting data to screen
	 */
	protected function onBeforeDisplay()
	{
		// By default we clean up the whole output buffer
		@ob_clean();
	}

	protected function includeTemplate()
	{
		// Do I have a layout? Great!
		if (parent::includeTemplate())
		{
			return;
		}

		// If not, let's JSON-encode the output and automatically render it
		$contents = json_encode($this->items);
		@ob_end_clean();

		echo '###' . $contents . '###';

		die();
	}

	/**
	 * Fetch the contents and send it to screen. Then close the application to avoid corrupted data
	 */
	protected function onAfterDisplay()
	{
		// By default let's kill the application
		$contents = @ob_get_clean();
		@ob_end_clean();

		echo '###' . $contents . '###';

		die();
	}
}
