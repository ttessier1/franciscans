<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var $this \Akeeba\AdminTools\Admin\View\Redirections\Html */
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=Redirections">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_REDIRS_EDIT');?>
</h1>

<section class="akeeba-panel">
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=Redirections" method="post" class="akeeba-form--horizontal">
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_DEST')?></label>
			<div class="akeeba-input-group">
				<span><?= site_url() ?>/</span>
                <input type="text" name="dest" value="<?php echo isset($this->item) ? $this->escape($this->item->dest) : '' ?>" />
            </div>
			<p class="akeeba-help-text">
				<?php echo Language::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_DEST_DESC')?>
			</p>
        </div>

        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE')?></label>
			<input type="text" name="source" value="<?php echo isset($this->item) ? $this->escape($this->item->source) : '' ?>" />
			<p class="akeeba-help-text">
				<?php echo Language::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_SOURCE_DESC')?>
			</p>
        </div>

        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_KEEPURLPARAMS')?></label>
			<?php echo Select::keepUrlParamsList('keepurlparams', null, isset($this->item) ? $this->item->keepurlparams : '')?>
			<p class="akeeba-help-text">
				<?php echo Language::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_KEEPURLPARAMS_DESC')?>
			</p>
        </div>

        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_PUBLISHED')?></label>
			<?php echo SelectHelper::booleanswitch('published', isset($this->item) ? $this->item->published : 1)?>
			<p class="akeeba-help-text">
				<?php echo Language::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_PUBLISHED_DESC')?>
			</p>
        </div>

		<div class="akeeba-form-group--actions">
			<div>
				<input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
				<a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=Redirections">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
				</a>
			</div>
		</div>

        <input type="hidden" name="view" value="Redirections"/>
        <input type="hidden" name="task" value="save"/>
        <input type="hidden" name="id" value="<?php echo isset($this->item) ? $this->item->id : '' ?>" />
		<?php wp_nonce_field('postRedirections') ?>
	</form>
</section>
