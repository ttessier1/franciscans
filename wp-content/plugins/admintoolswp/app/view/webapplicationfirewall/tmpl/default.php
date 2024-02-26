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
        <?php echo Language::_('COM_ADMINTOOLS_TITLE_WAF'); ?>
    </h1>

    <div class="akeeba-grid akeeba-panel">
        <?php if ($this->hasHtaccess): ?>
        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=OptimizeWAF" class="akeeba-action--orange">
            <span class="akion-flash"></span>
			<?php echo Language::_('COM_ADMINTOOLS_TITLE_OPTIMIZEWAF') ?>
        </a>
        <?php endif; ?>

        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ConfigureWAF" class="akeeba-action--grey">
            <span class="akion-settings"></span>
            <?php echo Language::_('COM_ADMINTOOLS_TITLE_WAFCONFIG') ?>
        </a>

        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ExceptionsFromWAF" class="akeeba-action--teal">
            <span class="akion-funnel"></span>
			<?php echo Language::_('COM_ADMINTOOLS_TITLE_WAFEXCEPTIONS'); ?>
        </a>

        <?php if (ADMINTOOLSWP_PRO): ?>
            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=WhitelistedAddresses" class="akeeba-action--teal">
                <span class="akion-ios-paper"></span>
                <?php echo Language::_('COM_ADMINTOOLS_TITLE_IPWL'); ?>
            </a>

            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=BlacklistedAddresses" class="akeeba-action--red">
                <span class="akion-ios-paper-outline"></span>
                <?php echo Language::_('COM_ADMINTOOLS_TITLE_IPBL'); ?>
            </a>

            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=BadWords" class="akeeba-action--red">
                <span class="akion-flag"></span>
                <?php echo Language::_('COM_ADMINTOOLS_TITLE_BADWORDS'); ?>
            </a>
        <?php endif; ?>

        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=SecurityExceptions" class="akeeba-action--grey">
            <span class="akion-stats-bars"></span>
            <?php echo Language::_('COM_ADMINTOOLS_TITLE_LOG'); ?>
        </a>

	    <?php if (ADMINTOOLSWP_PRO): ?>
            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=AutoBannedAddresses" class="akeeba-action--red">
                <span class="akion-close-circled"></span>
                <?php echo Language::_('COM_ADMINTOOLS_TITLE_IPAUTOBAN'); ?>
            </a>

            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=IPAutoBanHistories" class="akeeba-action--red">
                <span class="akion-close-circled"></span>
                <?php echo Language::_('COM_ADMINTOOLS_TITLE_IPAUTOBANHISTORY'); ?>
            </a>

            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=UnblockIP" class="akeeba-action--orange">
                <span class="akion-unlocked"></span>
			    <?php echo Language::_('COM_ADMINTOOLS_TITLE_UNBLOCKIP'); ?>
            </a>

            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=WAFEmailTemplates" class="akeeba-action--grey">
                <span class="akion-email"></span>
			    <?php echo Language::_('COM_ADMINTOOLS_TITLE_WAFEMAILTEMPLATES'); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
