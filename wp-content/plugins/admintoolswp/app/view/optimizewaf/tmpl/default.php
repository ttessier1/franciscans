<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;

/** @var    $this   Akeeba\AdminTools\Admin\View\OptimizeWaf\Html */

// Protect from unauthorized access
defined('ADMINTOOLSINC') or die;

?>
<h1>
	<a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall">
		<span class="akion-chevron-left"></span><span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
	</a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_OPTIMIZEWAF'); ?>
</h1>
<section class="akeeba-panel">
    <?php if ($this->enabled):?>
        <div class="akeeba-block--info">
            <?php echo Language::_('COM_ADMINTOOLS_OPTIMIZEWAF_ENABLED')?>
        </div>
    <?php /* We have files in place for auto-prepend mode but it's not running. Warn the user */?>
    <?php elseif(!$this->configured):?>
        <div class="akeeba-block--warning">
            <?php echo Language::_('COM_ADMINTOOLS_OPTIMIZEWAF_NOTRUNNING')?>
        </div>
    <?php endif; ?>
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=OptimizeWaf" id="akeeba-form" method="post" class="akeeba-form--horizontal">
		<div class="akeeba-form-group">
			<label><?php echo Language::_('COM_ADMINTOOLS_OPTIMIZEWAF_SERVERTECH'); ?></label>
			<div>
				<?php echo Select::environment('environment', $this->environment)?>
			</div>
		</div>

		<p class="submit">
			<?php if ($this->enabled):?>
                <input type="submit" class="akeeba-btn--red" onclick="document.getElementById('akeeba-task').value = 'disable';return true" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_DISABLE'); ?>"/>
			<?php endif;?>

			<input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_ENABLE'); ?>"/>

			<a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall">
				<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
			</a>
		</p>

		<input type="hidden" name="view" value="OptimizeWaf"/>
		<input type="hidden" id="akeeba-task" name="task" value="create"/>
		<?php wp_nonce_field('postOptimizeWaf', '_wpnonce', false) ?>
	</form>

</section>
