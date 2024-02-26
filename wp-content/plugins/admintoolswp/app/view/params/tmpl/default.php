<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;
?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
        <?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
    <?php echo Language::_('COM_ADMINTOOLS_TITLE_PARAMS'); ?>
</h1>

<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=Params" method="post" class="akeeba-form">
    <div class="akeeba-panel--primary">
        <header class="akeeba-block-header">
            <h3><?php echo Language::_('COM_ADMINTOOLS_PARAMS_SETTINGS'); ?></h3>
        </header>

        <?php include __DIR__.'/params.php';?>
    </div>

    <div class="akeeba-panel--primary">
        <header class="akeeba-block-header">
            <h3><?php echo Language::_('COM_ADMINTOOLS_PARAMS_UPDATES'); ?></h3>
        </header>

		<?php include __DIR__.'/updates.php';?>
    </div>

    <p class="submit">
        <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>

        <a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=ControlPanel">
            <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL')?>
        </a>
    </p>

    <input type="hidden" name="view" value="Params"/>
    <input type="hidden" name="task" value="save"/>
	<?php wp_nonce_field('postParams') ?>
</form>
