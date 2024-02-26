<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;

/** @var $this \Akeeba\AdminTools\Admin\View\WAFEmailTemplates\Html */

defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WAFEmailTemplates">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_WAFEMAILTEMPLATES_EDIT');?>
</h1>

<section class="akeeba-panel">
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=WAFEmailTemplates" method="post" class="akeeba-form--horizontal">
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_SELECT')?></label>
            <div>
				<?php echo Select::reasons(isset($this->item) ? $this->item->reason : 'all', 'reason', array('all' => 1, 'misc' => 1)); ?>
            </div>
        </div>

        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_SUBJECT_LBL')?></label>
            <div>
                <input type="text" size="30" class="input-xxlarge" id="subject_field" name="subject"
                       value="<?php echo isset($this->item) ? $this->escape($this->item->subject) : ''; ?>"/>
                <p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_SUBJECT_DESC'); ?></p>
            </div>
        </div>

        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_PUBLISHED')?></label>
            <div>
				<?php echo Select::published(isset($this->item) ? $this->item->enabled : 1, 'enabled'); ?>
            </div>
        </div>

        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_SENDLIMIT_LBL')?></label>
            <div>
                <input class="input-mini" type="text" size="5" name="email_num"
                       value="<?php echo isset($this->item) ? (int) $this->item->email_num : 5; ?>"/>
                <span><?php echo Language::_('COM_ADMINTOOLS_WAFEMAILTEMPLATES_NUMFREQ'); ?></span>
                <input class="input-mini" type="text" size="5" name="email_numfreq"
                       value="<?php echo isset($this->item) ? (int)$this->item->email_numfreq : 1; ?>"/>
				<?php echo Select::trsfreqlist('email_freq', array('class' => 'input-small'), isset($this->item) ? $this->item->email_freq : 'hour'); ?>

                <p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_SENDLIMIT_DESC'); ?></p>
            </div>
        </div>

        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_TEMPLATE_LBL')?></label>
            <div>
				<?php
				$settings = array(
					'teeny' => true
				);

				$contents = str_replace('\r\n', '', isset($this->item) ? $this->item->template: '');

				wp_editor($contents, 'template', $settings);
				?>

                <p class="akeeba-help-text"><?php echo Language::_('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_TEMPLATE_DESC'); ?></p>
            </div>
        </div>

		<p class="submit">
			<input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
			<a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WAFEmailTemplates">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
            </a>
		</p>

        <input type="hidden" name="view" value="WAFEmailTemplates"/>
        <input type="hidden" name="task" value="save"/>
        <input type="hidden" name="admintools_waftemplate_id" value="<?php echo isset($this->item) ? $this->item->admintools_waftemplate_id : '' ?>" />
		<?php wp_nonce_field('postWAFEmailTemplates') ?>
	</form>
</section>
