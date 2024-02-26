<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;

/** @var $this \Akeeba\AdminTools\Admin\View\PassExpiration\Html */

defined('ADMINTOOLSINC') or die;

?>
<div>
	<h1>
		<a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
			<span class="akion-chevron-left"></span>
			<span class="aklogo-admintools-wp-small"></span>
			<?php echo Language::_('COM_ADMINTOOLS') ?>
		</a>
		<?php echo Language::_('COM_ADMINTOOLS_TITLE_PASSWORD_EXPIRATION'); ?>
	</h1>

	<section class="akeeba-panel">
		<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=PassExpiration" name="adminForm" id="adminForm" method="post" class="akeeba-form--horizontal">
			<p><?php echo Language::_('COM_ADMINTOOLS_LBL_PASSEXPIRATION_DESCR')?></p>

            <div class="akeeba-form-group">
                <label for="passexp"><?php echo Language::_('COM_ADMINTOOLS_LBL_PASSEXPIRATION_DAYS') ?></label>

                <div>
                    <input type="text" id="passexp" name="passexp" class="akeeba-input-mini" value="<?php echo $this->wafconfig['passexp'] ?>" />
                </div>
            </div>

            <div class="akeeba-form-group">
                <label><?php echo Language::_('COM_ADMINTOOLS_LBL_PASSEXPIRATION_ROLES') ?></label>

                <div>
                    <?php echo Select::roles('passexp_roles[]', $this->wafconfig['passexp_roles'], array('hideEmpty' => true, 'multiple' => true, 'size' => 10))?>
                </div>
            </div>

			<p>
				<input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_PASSEXPIRATION_SET')?>"/>
				<a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WPTools">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
				</a>
			</p>

			<input type="hidden" name="view" value="PassExpiration"/>
			<input type="hidden" name="task" id="task" value="save"/>
			<?php wp_nonce_field('postPassExpiration') ?>
		</form>
	</section>
</div>

