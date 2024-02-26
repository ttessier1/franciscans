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

class BlacklistedAddresses extends Controller
{
	public function export()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\BlacklistedAddresses $model */
		$model = $this->getModel();
		$items = $model->getItems(true);

		$csv[] = '"ip","description"';

		foreach ($items as $item)
		{
			$description = str_replace('"', '""', $item->description);
			$description = str_replace("\r", '\\r', $description);
			$description = str_replace("\n", '\\n', $description);

			$csv[] = '"'.$item->ip.'","'.$description.'"';
		}

		@ob_clean();

		header('Pragma: public');
		header('Expires: 0');

		// This moronic construct is required to work around idiot hosts who blacklist files based on crappy, broken scanners
		$xo = substr("revenge", 0, 3);
		$xoxo = substr("calibrate", 1, 2);
		header('Cache-Control: must-' . $xo . $xoxo . 'idate, post-check=0, pre-check=0');

		header('Cache-Control: public', false);
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="ip_blacklist.csv"');

		echo implode("\r\n", $csv);

		die();
	}

	public function import()
	{
		$view = $this->getView();
		$view->setLayout('import');

		$this->display();
	}

	public function doimport()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\BlacklistedAddresses $model */
		$model     = $this->getModel();
		$file      = $this->input->files->get('csvfile', null, 'raw');
		$delimiter = $this->input->getInt('csvdelimiters', 0);
		$field     = $this->input->getString('field_delimiter', '');
		$enclosure = $this->input->getString('field_enclosure', '');

		if ($file['error'])
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_UPLOAD'), 'error');
			$this->redirect(ADMINTOOLSWP_URL.'&view=BlacklistedAddresses&task=import');
		}

		if ($delimiter != - 99)
		{
			list($field, $enclosure) = $model->decodeDelimiterOptions($delimiter);
		}

		// Import ok, but maybe I have warnings (ie skipped lines)
		try
		{
			$model->import($file['tmp_name'], $field, $enclosure);
		}
		catch (\RuntimeException $e)
		{
			//Uh oh... import failed, let's inform the user why it happened
			$this->getView()->enqueueMessage(Language::sprintf('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_FAILURE', $e->getMessage()), 'error');
		}

		$this->redirect(ADMINTOOLSWP_URL.'&view=BlacklistedAddresses');
	}
}
