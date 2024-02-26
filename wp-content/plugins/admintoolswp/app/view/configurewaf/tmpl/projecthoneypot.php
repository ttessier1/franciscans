<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

defined('ADMINTOOLSINC') or die;
?>
<div class="akeeba-form-group">
    <label for="httpblenable">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLENABLE'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('httpblenable', $this->wafconfig['httpblenable']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLENABLE_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="bbhttpblkey">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BBHTTPBLKEY'); ?>
    </label>

	<input type="text" size="45" name="bbhttpblkey" id="bbhttpblkey" value="<?php echo $this->escape($this->wafconfig['bbhttpblkey']); ?>"/>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BBHTTPBLKEY_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="httpblthreshold">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLTHRESHOLD'); ?>
    </label>

	<input type="text" size="5" name="httpblthreshold" id="httpblthreshold" value="<?php echo $this->escape($this->wafconfig['httpblthreshold']); ?>"/>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLTHRESHOLD_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="httpblmaxage">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLMAXAGE'); ?>
    </label>

	<input type="text" size="5" name="httpblmaxage" id="httpblmaxage" value="<?php echo $this->escape($this->wafconfig['httpblmaxage']); ?>"/>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLMAXAGE_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="httpblblocksuspicious">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLBLOCKSUSPICIOUS'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('httpblblocksuspicious', $this->wafconfig['httpblblocksuspicious']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_HTTPBLBLOCKSUSPICIOUS_TIP'); ?>
	</p>
</div>
