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
    <label for="custom403msg">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_LABEL'); ?>
    </label>

	<input type="text" class="regular-text" name="custom403msg"
		   value="<?php echo htmlentities($this->wafconfig['custom403msg']) ?>"
		   title="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_DESC'); ?>"/>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_DESC'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="troubleshooteremail">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TROUBLESHOOTEREMAIL'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('troubleshooteremail', $this->wafconfig['troubleshooteremail']); ?>
    <p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TROUBLESHOOTEREMAIL_TIP'); ?>
    </p>
</div>

<!--<div class="akeeba-form-group">
    <label for="use403view">
        <?php /*echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_USE403VIEW'); */?>
    </label>

	<?php /*echo Select::booleanlist('use403view', array(), $this->wafconfig['use403view']); */?>
	<p class="akeeba-help-text">
		<?php /*echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_USE403VIEW_TIP'); */?>
	</p>
</div>-->
