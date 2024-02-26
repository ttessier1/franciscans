<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var  Akeeba\AdminTools\Admin\View\ControlPanel\Html $this */

use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;

include __DIR__.'/needsipworkarounds.php';

?>

<?php if ($this->frontEndSecretWordIssue): ?>
	<div class="akeeba-block--failure">
		<h3><?php echo Language::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_HEADER'); ?></h3>
		<p><?php echo Language::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_INTRO'); ?></p>
		<p><?php echo $this->frontEndSecretWordIssue; ?></p>
		<p>
			<?php echo Language::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_WHATTODO_WORDPRESS'); ?>
			<?php echo Language::sprintf('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_WHATTODO_COMMON', $this->newSecretWord); ?>
		</p>
		<p>
			<a class="akeeba-btn"
			   href="<?php echo ADMINTOOLSWP_URL; ?>&view=ControlPanel&task=resetSecretWord&_wpnonce=<?php echo wp_create_nonce('getControlPanel'); ?>">
				<span class="akion-checkmark-round"></span>
				<?php echo Language::_('COM_ADMINTOOLS_CONTROLPANEL_BTN_FESECRETWORD_RESET'); ?>
			</a>
		</p>
	</div>
<?php endif; ?>

<?php
// Obsolete PHP version check
$minPHPVersion = '7.4.0';
$softwareName  = 'Admin Tools for WordPress';

include __DIR__ . '/phpversion_warning.php';
?>

<?php if ($this->oldVersion): ?>
	<div class="akeeba-block--info">
		<strong><?php echo Language::_('COM_ADMINTOOLS_ERR_CONTROLPANEL_OLDVERSION'); ?></strong>
	</div>
<?php endif; ?>

<?php if ($this->needsdlid): ?>
	<div class="akeeba-block--warning">
		<h3>
			<?php echo Language::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_MUSTENTERDLID'); ?>
		</h3>
		<p>
			<?php echo Language::sprintf('COM_ADMINTOOLS_LBL_CONTROLPANEL_NEEDSDLID','https://www.akeeba.com/download/official/add-on-dlid.html'); ?>
		</p>
		<form name="dlidform" action="<?php echo ADMINTOOLSWP_URL; ?>&view=ControlPanel" method="post" class="akeeba-form--inline">
			<input type="hidden" name="view" value="ControlPanel" />
			<input type="hidden" name="task" value="applydlid" />
			<?php echo wp_nonce_field('postControlPanel') ?>
	<span>
		<?php echo Language::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_PASTEDLID'); ?>
	</span>
			<input type="text" name="dlid" placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_PARAMS_DOWNLOADID'); ?>" />
			<button type="submit" class="akeeba-btn">
				<span class="akion-checkmark-round"></span>
				<?php echo Language::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_APPLYDLID'); ?>
			</button>
		</form>
	</div>
<?php endif; ?>

<div id="updateNotice"></div>
