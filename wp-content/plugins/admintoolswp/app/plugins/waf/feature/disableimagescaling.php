<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureDisableimagescaling extends AtsystemFeatureAbstract
{
	protected $loadOrder = 72;

	public function isEnabled()
	{
		return $this->cparams->getValue('disable_image_scaling', 0);
	}

	/**
	 * On our custom hook, let's ask WordPress to disable image scaling
	 */
	public function onCustomHooks()
	{
		add_filter( 'big_image_size_threshold', '__return_false' );
	}
}
