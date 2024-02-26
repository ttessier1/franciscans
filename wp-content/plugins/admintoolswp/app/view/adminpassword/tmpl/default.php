<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

defined('ADMINTOOLSINC') or die;
?>

<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
        <?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
    <?php echo Language::_('COM_ADMINTOOLS_TITLE_ADMINPW');?>
</h1>

<section class="akeeba-panel">
	<div class="akeeba-panel--teal">
		<header class="akeeba-block-header">
			<h3><?= Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_HOWITWORKS') ?></h3>
		</header>
		<p>
			<?php echo Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_INFO'); ?>
		</p>

		<p class="akeeba-block--warning">
			<?php echo Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_WARN'); ?>
		</p>
	</div>

	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=AdminPassword" name="adminForm" id="adminForm" method="post" class="akeeba-form--horizontal">
		<div class="akeeba-form-group">
			<label for="resetErrorPages"><?php echo Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_RESETERRORPAGES'); ?></label>
			<?php echo SelectHelper::booleanswitch('resetErrorPages', $this->resetErrorPages) ?>
			<p class="akeeba-help-text">
				<?= Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_RESETERRORPAGES_HELP') ?>
			</p>
		</div>

		<div class="akeeba-form-group">
			<label for="username"><?php echo Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_USERNAME'); ?></label>
			<input type="text" name="username" id="username" value="<?php echo $this->escape($this->username); ?>" autocomplete="off"/>
			<p class="akeeba-help-text">
				<?= Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_USERNAME_HELP') ?>
			</p>
		</div>

		<div class="akeeba-form-group">
			<label for="password"><?php echo Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD'); ?></label>
			<input type="password" name="password" id="password" value="<?php echo $this->escape($this->password); ?>" autocomplete="off"/>
			<p class="akeeba-help-text">
				<?= Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD_HELP') ?>
			</p>
		</div>

		<div class="akeeba-form-group">
			<label for="password2"><?php echo Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD2'); ?></label>
			<input type="password" name="password2" id="password2" value="<?php echo $this->escape($this->password); ?>" autocomplete="off"/>
			<p class="akeeba-help-text">
				<?= Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD2_HELP') ?>
			</p>
		</div>

		<div class="akeeba-form-group--pull-right">
		</div>

		<div class="form-actions">
			<div class="akeeba-form-group--actions">
			</div>

			<button type="submit" class="akeeba-btn--orange">
				<span class="akion-android-lock"></span>
				<?php echo Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PROTECT'); ?>
			</button>
			<?php if ($this->adminLocked): ?>
				<a class="akeeba-btn--green"
				   href="<?php echo ADMINTOOLSWP_URL; ?>&view=AdminPassword&task=unprotect&_wpnonce=<?php echo wp_create_nonce('getAdminPassword') ?>"
				>
					<span class="akion-android-unlock"></span>
					<?php echo Language::_('COM_ADMINTOOLS_LBL_ADMINPASSWORD_UNPROTECT'); ?>
				</a>
			<?php endif; ?>
			<a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>">Cancel</a>
		</div>

        <input type="hidden" name="view" value="AdminPassword"/>
        <input type="hidden" name="task" id="task" value="protect"/>
		<?php wp_nonce_field('postAdminPassword', '_wpnonce', false) ?>
	</form>
</section>
