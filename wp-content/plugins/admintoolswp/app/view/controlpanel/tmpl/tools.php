<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\ControlPanel\Html */
use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;

?>
<div class="akeeba-panel--default">
    <header class="akeeba-block-header">
        <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_TOOLS') ?></h3>
    </header>

    <div class="akeeba-grid">
        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=Params" class="akeeba-action--teal">
            <span class="akion-ios-gear"></span>
            <?php echo Language::_('COM_ADMINTOOLS_TITLE_PARAMS') ?>
        </a>

        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=WPTools" class="akeeba-action--grey">
            <span class="akion-social-wordpress"></span>
			<?php echo Language::_('COM_ADMINTOOLS_TITLE_WPTOOLS') ?>
        </a>

        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=HttpsTools" class="akeeba-action--teal">
            <span class="akion-link"></span>
            <?php echo Language::_('COM_ADMINTOOLS_TITLE_HTTPSTOOLS'); ?>
        </a>

        <?php if ($this->enable_dbtools): ?>
            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=Databasetools" class="akeeba-action--teal">
                <span class="akion-wand"></span>
                <?php echo Language::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB'); ?><br/>
            </a>
        <?php endif; ?>

        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=Redirections" class="akeeba-action--teal">
            <span class="akion-shuffle"></span>
            <?php echo Language::_('COM_ADMINTOOLS_TITLE_REDIRS'); ?>
        </a>

        <?php if ($this->isPro): ?>
        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ImportAndExport&task=export" class="akeeba-action--teal">
            <span class="akion-share"></span>
            <?php echo Language::_('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS') ?>
        </a>

        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ImportAndExport&task=import" class="akeeba-action--teal">
            <span class="akion-archive"></span>
            <?php echo Language::_('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS') ?>
        </a>
        <?php endif; ?>

    <?php if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN'): ?>
        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ConfigureFixPermissions" class="akeeba-action--orange">
            <span class="akion-ios-gear"></span>
			<?php echo Language::_('COM_ADMINTOOLS_TITLE_FIXPERMSCONFIG'); ?><br/>
        </a>

		<?php if ($this->enable_fixperms): ?>
            <a id="fixperms" href="<?php echo ADMINTOOLSWP_URL; ?>&view=FixPermissions&tmpl=component" class="akeeba-action--orange">
                <span class="akion-wand"></span>
				<?php echo Language::_('COM_ADMINTOOLS_TITLE_FIXPERMS'); ?>
            </a>
		<?php endif; ?>
    <?php endif; ?>
    </div>
</div>
