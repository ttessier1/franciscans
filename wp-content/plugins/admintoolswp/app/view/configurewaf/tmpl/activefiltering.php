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
    <label for="sqlishield">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SQLISHIELD'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('sqlishield', $this->wafconfig['sqlishield']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SQLISHIELD_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="rfishield">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_RFISHIELD'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('rfishield', $this->wafconfig['rfishield']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_RFISHIELD_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="phpshield">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_PHPSHIELD'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('phpshield', $this->wafconfig['phpshield']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_PHPSHIELD_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="uploadshield">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_UPLOADSHIELD'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('uploadshield', $this->wafconfig['uploadshield']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_UPLOADSHIELD_TIP'); ?>
	</p>
</div>

<?php if(ADMINTOOLSWP_PRO): ?>
<div class="akeeba-form-group">
    <label for="antispam">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ANTISPAM'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('antispam', $this->wafconfig['antispam']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::sprintf('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ANTISPAM_TIP', ADMINTOOLSWP_URL.'&view=BadWords'); ?>
	</p>
</div>
<?php endif; ?>