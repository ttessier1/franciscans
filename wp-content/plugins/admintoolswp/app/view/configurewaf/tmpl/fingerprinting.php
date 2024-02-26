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
    <label for="custgenerator">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CUSTGENERATOR'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('custgenerator', $this->wafconfig['custgenerator']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CUSTGENERATOR_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="generator">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_GENERATOR'); ?>
    </label>

	<input type="text" size="45" id="generator" name="generator" value="<?php echo $this->escape($this->wafconfig['generator']); ?>">
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_GENERATOR_TIP'); ?>
	</p>
</div>
