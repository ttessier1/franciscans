<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;

?>
<div class="akeeba-form-group">
    <label for="neverblockips">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_NEVERBLOCKIPS'); ?>
    </label>

	<input class="regular-text" type="text" size="50" name="neverblockips" id="neverblockips" value="<?php echo $this->escape($this->wafconfig['neverblockips']); ?>"/>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_NEVERBLOCKIPS_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="whitelist_domains">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_WHITELIST_DOMAINS'); ?>
    </label>

	<input type="text" class="regular-text" size="50" name="whitelist_domains" id="whitelist_domains" value="<?php echo $this->escape($this->wafconfig['whitelist_domains']); ?>">
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_WHITELIST_DOMAINS_TIP'); ?>
	</p>
</div>
