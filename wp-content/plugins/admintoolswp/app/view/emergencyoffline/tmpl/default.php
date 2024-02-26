<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var    $this   Akeeba\AdminTools\Admin\View\EmergencyOffline\Html */

// Protect from unauthorized access
defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
        <?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
    <?php echo Language::_('COM_ADMINTOOLS_TITLE_EOM'); ?>
</h1>
<section class="akeeba-panel">
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=EmergencyOffline" name="adminForm" id="adminForm" class="akeeba-form" method="post">
		<input type="hidden" name="view" value="EmergencyOffline"/>
		<input type="hidden" name="task" value="offline"/>
        <p>
		    <input type="submit" class="akeeba-btn--red--big" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_SETOFFLINE'); ?>"/>
        </p>
		<?php wp_nonce_field('postEmergencyOffline', '_wpnonce', false) ?>
	</form>

<?php if ( ! ($this->offline)): ?>
	<p><?php echo Language::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREAPPLY'); ?></p>
	<p><?php echo Language::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREAPPLYMANUAL'); ?></p>
	<pre><?php echo $this->htaccess ?></pre>
<?php endif; ?>

<?php if ($this->offline): ?>
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=EmergencyOffline" name="adminForm" id="adminForm" class="akeeba-form" method="post">
		<input type="hidden" name="view" value="EmergencyOffline"/>
		<input type="hidden" name="task" value="online"/>
		<p><input type="submit" class="akeeba-btn--green--big" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_UNAPPLY'); ?>"/></p>
		<?php wp_nonce_field('postEmergencyOffline', '_wpnonce', false) ?>
	</form>
	<p><?php echo Language::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREUNAPPLY'); ?></p>
	<p><?php echo Language::_('COM_ADMINTOOLS_LBL_EMERGENCYOFFLINE_PREUNAPPLYMANUAL'); ?></p>
<?php endif; ?>
</section>
