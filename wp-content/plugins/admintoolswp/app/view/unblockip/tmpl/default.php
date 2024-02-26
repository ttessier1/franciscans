<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var    $this   Akeeba\AdminTools\Admin\View\UnblockIP\Html */

defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_UNBLOCKIP');?>
</h1>


<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=UnblockIP" name="adminForm" id="adminForm" method="post" class="akeeba-form--horizontal">
    <p class="akeeba-block--info">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_INFO')?>
    </p>
	<div>
		<div class="akeeba-form-group">
			<label><?php echo Language::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_CHOOSE_IP')?></label>
			<input type="text" value="" name="ip" />
		</div>

		<div class="akeeba-form-group--pull-right">
			<div>
				<input type="submit" class="akeeba-btn--primary--big" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_IP'); ?>"/>
			</div>
		</div>
	</div>

    <input type="hidden" name="view" value="UnblockIP"/>
    <input type="hidden" name="task" value="unblock"/>
	<?php wp_nonce_field('postUnblockIP', '_wpnonce', false) ?>
</form>
