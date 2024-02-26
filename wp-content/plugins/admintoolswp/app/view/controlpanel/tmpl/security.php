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

<div class="akeeba-panel--primary">
    <header class="akeeba-block-header">
        <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_SECURITY'); ?></h3>
    </header>
    <div class="akeeba-grid">
        <?php if ($this->htMakerSupported): ?>
        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=EmergencyOffline" class="akeeba-action--red">
            <span class="akion-power"></span>
            <?php echo Language::_('COM_ADMINTOOLS_TITLE_EOM'); ?>
        </a>
        <?php endif;?>

        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=MasterPassword" class="akeeba-action--orange">
            <span class="akion-key"></span>
            <?php echo Language::_('COM_ADMINTOOLS_TITLE_MASTERPW'); ?>
        </a>

        <?php if ($this->htMakerSupported): ?>
            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=AdminPassword" class="akeeba-action--orange">
                <span class="akion-locked"></span>
                <?php echo Language::_('COM_ADMINTOOLS_TITLE_ADMINPW'); ?>
            </a>
        <?php endif; ?>

        <?php if ($this->isPro): ?>
            <?php if ($this->htMakerSupported): ?>
                <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=HtaccessMaker" class="akeeba-action--teal">
                    <span class="akion-document-text"></span>
                    <?php echo Language::_('COM_ADMINTOOLS_TITLE_HTMAKER'); ?>
                </a>
            <?php endif; ?>

            <?php if ($this->webConfMakerSupported): ?>
                <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebConfigMaker" class="akeeba-action--teal">
                    <span class="akion-document-text"></span>
                    <?php echo Language::_('COM_ADMINTOOLS_TITLE_WCMAKER'); ?>
                </a>
            <?php endif; ?>

            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=MalwareDetection" class="akeeba-action--red">
                <span class="akion-bug"></span>
                <?php echo Language::_('COM_ADMINTOOLS_TITLE_MALWAREDETECTION') ?>
            </a>
        <?php endif; ?>

        <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall" class="akeeba-action--grey">
            <span class="akion-close-circled"></span>
            <?php echo Language::_('COM_ADMINTOOLS_TITLE_WAF') ?>
        </a>
    </div>
</div>
