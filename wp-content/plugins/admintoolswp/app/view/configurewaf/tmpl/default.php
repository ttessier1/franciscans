<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this Akeeba\AdminTools\Admin\View\ConfigureWAF\Html */

defined('ADMINTOOLSINC') or die;

$tabclass = $this->longConfig ? '' : 'akeeba-tabs';
?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
        <?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
    <?php echo Language::_('COM_ADMINTOOLS_TITLE_WAFCONFIG');?>
</h1>

<section class="akeeba-panel">
    <form action="<?php echo ADMINTOOLSWP_URL; ?>&view=ConfigureWAF" method="post" class="akeeba-form--horizontal">
        <div id="configure_waf" class="<?php echo $tabclass?>">
			<?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_BASICSETTINGS'); ?></h4>
			<?php else:?>
                <label for="base" class="active">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_BASICSETTINGS'); ?>
                </label>
			<?php endif;?>
            <section id="base">
				<?php include (__DIR__.'/base.php'); ?>
            </section>

			<?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_ACTIVEFILTERING'); ?></h4>
			<?php else:?>
                <label for="activefiltering">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_ACTIVEFILTERING'); ?>
                </label>
			<?php endif;?>
            <section id="activefiltering">
				<?php include (__DIR__.'/activefiltering.php'); ?>
            </section>

            <?php if (ADMINTOOLSWP_PRO): ?>
            <?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_WPHARDENING'); ?></h4>
			<?php else:?>
                <label for="wphardening">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_WPHARDENING'); ?>
                </label>
			<?php endif;?>
            <section id="wphardening">
				<?php include (__DIR__.'/wphardening.php'); ?>
            </section>
            <?php endif; ?>

			<?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_FINGERPRINTING'); ?></h4>
			<?php else:?>
                <label for="fingerprinting">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_FINGERPRINTING'); ?>
                </label>
			<?php endif;?>
            <section id="fingerprinting">
				<?php include (__DIR__.'/fingerprinting.php'); ?>
            </section>

			<?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PROJECTHONEYPOT'); ?></h4>
			<?php else:?>
                <label for="projecthoneypot">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PROJECTHONEYPOT'); ?>
                </label>
			<?php endif;?>
            <section id="projecthoneypot">
				<?php include (__DIR__.'/projecthoneypot.php'); ?>
            </section>

			<?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_EXCEPTIONS'); ?></h4>
			<?php else:?>
                <label for="exceptions">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_EXCEPTIONS'); ?>
                </label>
			<?php endif;?>
            <section id="exceptions">
				<?php include (__DIR__.'/exceptions.php'); ?>
            </section>

            <?php if (ADMINTOOLSWP_PRO): ?>
			<?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSR'); ?></h4>
			<?php else:?>
                <label for="tsr">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSR'); ?>
                </label>
			<?php endif;?>
            <section id="tsr">
				<?php include (__DIR__.'/tsr.php'); ?>
            </section>
            <?php endif; ?>

			<?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_LOGGINGANDREPORTING'); ?></h4>
			<?php else:?>
                <label for="logging">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_LOGGINGANDREPORTING'); ?>
                </label>
			<?php endif;?>
            <section id="logging">
				<?php include (__DIR__.'/logging.php'); ?>
            </section>

			<?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_HEADER'); ?></h4>
			<?php else:?>
                <label for="custom">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_CUSTOMMESSAGE_HEADER'); ?>
                </label>
			<?php endif;?>
            <section id="custom">
				<?php include (__DIR__.'/custom.php'); ?>
            </section>
        </div>

        <p class="submit">
            <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
            <a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall">
		        <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
            </a>
        </p>

        <input type="hidden" name="view" value="ConfigureWAF"/>
        <input type="hidden" name="task" value="save"/>
		<?php wp_nonce_field('postConfigureWAF') ?>
    </form>
</section>

<script>
    var admintoolswp_myIP = '<?php echo $this->myIP?>';
    <?php if (!$this->longConfig): ?>
    jQuery(document).ready(function(){
        akeeba.fef.tabs();
    });
    <?php endif; ?>
</script>
