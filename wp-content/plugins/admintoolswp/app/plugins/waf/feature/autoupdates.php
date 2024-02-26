<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureAutoupdates extends AtsystemFeatureAbstract
{
	protected $loadOrder = 1;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		// This feature is always enabled
		return true;
	}

	/**
	 * Tweak auto-update logic by
	 */
	public function onWordPressLoad()
	{
		$this->autoupdates_core();
		$this->autoupdates_plugins();
		$this->autoupdates_themes();
		$this->autoupdates_translations();
	}

	private function autoupdates_core()
	{
		$value = $this->cparams->getValue('core_autoupdates', 1);

		// 1 mean 'minor', WordPress default, so we do nothing to change its default behavior
		if ($value == 1)
		{
			return;
		}

		// Disable core autoupdates
		if (!$value)
		{
			add_filter( 'auto_update_core', '__return_false' );

			return;
		}

		// Enable autoupdates for major versions, too
		if ($value == 2)
		{
			add_filter( 'allow_major_auto_core_updates', '__return_true');

			return;
		}
	}

	private function autoupdates_plugins()
	{
		// WordPress default, so we do nothing to change its default behavior
		if ($this->cparams->getValue('autoupdate_plugins', 1))
		{
			return;
		}

		add_filter( 'auto_update_plugin', '__return_false');
	}

	private function autoupdates_themes()
	{
		// WordPress default, so we do nothing to change its default behavior
		if ($this->cparams->getValue('autoupdate_themes', 1))
		{
			return;
		}

		add_filter( 'auto_update_theme', '__return_false');
	}

	private function autoupdates_translations()
	{
		// WordPress default, so we do nothing to change its default behavior
		if ($this->cparams->getValue('autoupdate_translations', 1))
		{
			return;
		}

		add_filter( 'auto_update_translation', '__return_false');
	}
}
