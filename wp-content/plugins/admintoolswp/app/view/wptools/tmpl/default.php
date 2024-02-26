<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;

?>
<div>
    <h1>
        <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
            <span class="akion-chevron-left"></span>
            <span class="aklogo-admintools-wp-small"></span>
            <?php echo Language::_('COM_ADMINTOOLS') ?>
        </a>
        <?php echo Language::_('COM_ADMINTOOLS_TITLE_WPTOOLS'); ?>
    </h1>

    <div class="akeeba-grid akeeba-panel">
        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=Salts" class="akeeba-action--teal">
            <span class="akion-refresh"></span>
			<?php echo Language::_('COM_ADMINTOOLS_TITLE_SALTS'); ?><br/>
        </a>

        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=PassExpiration" class="akeeba-action--grey">
            <span class="akion-clock"></span>
			<?php echo Language::_('COM_ADMINTOOLS_TITLE_PASSWORD_EXPIRATION'); ?><br/>
        </a>

        <?php if (ADMINTOOLSWP_PRO): ?>
        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=AdvancedWPConfig" class="akeeba-action--orange">
            <span class="akion-speedometer"></span>
			<?php echo Language::_('COM_ADMINTOOLS_TITLE_ADVANCEDWPCONFIG'); ?><br/>
        </a>
        <?php endif; ?>

	    <?php if (ADMINTOOLSWP_PRO): ?>
            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=TempSuperUsers" class="akeeba-action--teal">
                <span class="akion-clock"></span>
			    <?php echo Language::_('COM_ADMINTOOLS_TITLE_TEMPSUPERUSERS'); ?><br/>
            </a>
	    <?php endif; ?>
    </div>
</div>
