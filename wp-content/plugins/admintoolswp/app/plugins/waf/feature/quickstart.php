<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Storage;

defined('ADMINTOOLSINC') or die;

/**
 * Detect if the Quick Start Wizard has ran (or Admin Tools has been manually configured). Otherwise display a message
 * reminding the user to run the wizard.
 */
class AtsystemFeatureQuickstart extends AtsystemFeatureAbstract
{
	protected $loadOrder = 999;

	public function onAdminNotices()
	{
		if (is_multisite())
		{
			return;
		}

		$this->commonNoticeCode();
	}

	public function onNetworkAdminNotices()
	{
		if (!is_multisite())
		{
			return;
		}

		$this->commonNoticeCode();
	}

	private function commonNoticeCode()
	{
		/** @var Storage $storage */
		$storage       = Storage::getInstance();
		$wizardHasRan  = $storage->getValue('quickstart', 0);
		$networkPrefix = is_multisite() ? '/network' : '';

		if ($wizardHasRan)
		{
			return;
		}

		$plugin_dir = get_option('admintoolswp_plugin_dir', 'admintoolswp');
		$plugin_url = admin_url() . $networkPrefix . '/admin.php?page=' . $plugin_dir . '/admintoolswp.php&view=QuickStart';

		$msg = Language::sprintf('COM_ADMINTOOLS_QUICKSTART_MSG_PLEASERUNWIZARD', $plugin_url);

		echo '<div class="notice update-nag">' . $msg . '</div>';
	}
} 
