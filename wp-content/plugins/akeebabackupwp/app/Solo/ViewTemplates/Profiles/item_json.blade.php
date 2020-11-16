<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Engine\Factory;

defined('_AKEEBA') or die();

/** @var \Solo\View\Profiles\Json $this */

/** @var \Solo\Model\Profiles $model */
$model = $this->getModel();

$data = $model->toArray();

if (substr($data['configuration'], 0, 12) == '###AES128###')
{
	// Load the server key file if necessary
	$key = Factory::getSecureSettings()->getKey();

	$data['configuration'] = Factory::getSecureSettings()->decryptSettings($data['configuration'], $key);
}

$defaultName = $this->input->get('view', 'joomla', 'cmd');
$filename = $this->input->get('basename', $defaultName, 'cmd');

$document = $this->container->application->getDocument();
$document->setName($filename);

echo json_encode($data);
