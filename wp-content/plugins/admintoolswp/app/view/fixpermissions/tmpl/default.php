<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\FixPermissions\Html */

defined('ADMINTOOLSINC') or die;

?>
<?php if ($this->more): ?>
	<h1><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_INPROGRESS'); ?></h1>
<?php else: ?>
	<h1><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DONE'); ?></h1>
<?php endif; ?>

	<div class="akeeba-progress">
        <div class="akeeba-progress-fill" style="width:<?php echo $this->percentage?>%;"></div>
        <div class="akeeba-progress-status">
            <?php echo $this->percentage?>%
        </div>
    </div>

	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=FixPermissions" name="adminForm" id="adminForm" method="post" class="akeeba-form">
		<input type="hidden" name="view" value="FixPermissions"/>
		<input type="hidden" name="task" value="run"/>
		<input type="hidden" name="tmpl" value="component"/>
		<?php wp_nonce_field('postFixPermissions') ?>
	</form>

<?php if (!$this->more): ?>
	<div class="akeeba-block--info" id="admintools-fixpermissions-autoclose">
		<p><?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_AUTOCLOSEIN3S'); ?></p>
	</div>
<?php endif; ?>
