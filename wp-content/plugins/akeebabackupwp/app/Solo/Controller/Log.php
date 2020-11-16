<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Controller;


class Log extends ControllerDefault
{
	/**
	 * Executes a given controller task. The onBefore<task> and onAfter<task>
	 * methods are called automatically if they exist.
	 *
	 * This method is overridden to add support for the profileid query parameter which switches the active
	 * backup profile.
	 *
	 * @param   string $task The task to execute, e.g. "browse"
	 *
	 * @return  null|bool  False on execution failure
	 *
	 * @throws  \Exception  When the task is not found
	 */
	public function execute($task)
	{
		// If the profile_id parameter is defined and it's a positive integer change the active profile
		$profile_id = $this->input->getInt('profileid', null);

		if (!empty($profile_id) && is_numeric($profile_id) && ($profile_id > 0))
		{
			\Awf\Application\Application::getInstance()->getContainer()->segment->profile = $profile_id;
		}

		// Execute the controller
		return parent::execute($task);
	}


	/**
	 * Allows the user to select the log origin to display or display the log file itself
	 *
	 * @return  void
	 */
	public function main()
	{
		$tag = $this->input->get('tag', null, 'cmd');
		$latest = $this->input->get('latest', false, 'int');
		
		if (empty($tag))
		{
			$tag = null;
		}

		/** @var \Solo\Model\Log $model */
		$model = $this->getModel();

		if ($latest)
		{
			$logFiles = $model->getLogFiles();
			$tag = array_shift($logFiles);
		}

		$model->setState('tag', $tag);

		$this->display();
	}

	/**
	 * Renders the log contents for use in an iFrame
	 *
	 * @return  void
	 */
	public function iframe()
	{
		$tag = $this->input->get('tag', null, 'cmd');

		if (empty($tag))
		{
			$tag = null;
		}

		$model = $this->getModel();
		$model->setState('tag', $tag);

		$this->display();
	}

	/**
	 * Downloads the log file as a plain text file
	 *
	 * @return  void
	 */
	public function download()
	{
		$tag = $this->input->get('tag', null, 'cmd');

		if (empty($tag))
		{
			$tag = null;
		}

		$asAttachment = $this->input->getBool('attachment', true);

		@ob_end_clean(); // In case some braindead plugin spits its own HTML
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Content-Description: File Transfer");
		header('Content-Type: text/plain');

		$inCMS    = $this->container->segment->get('insideCMS', false);
		$filename = 'Akeeba ' . ($inCMS ? 'Backup' : 'Solo') . ' Debug Log.txt';

		if ($asAttachment)
		{
			header('Content-Disposition: attachment; filename="' . $filename . '"');
		}

		/** @var \Solo\Model\Log $model */
		$model = $this->getModel();
		$model->setState('tag', $tag);

		$model->echoRawLog();

		@flush();
		$this->container->application->close();
	}

	public function inlineRaw()
	{
		$tag = $this->input->get('tag', null, 'cmd');

		if (empty($tag))
		{
			$tag = null;
		}

		/** @var \Solo\Model\Log $model */
		$model = $this->getModel();
		$model->setState('tag', $tag);
		$model->echoRawLog();
	}

} 
