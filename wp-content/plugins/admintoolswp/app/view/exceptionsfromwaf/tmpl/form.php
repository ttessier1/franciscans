<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

/** @var $this \Akeeba\AdminTools\Admin\View\ExceptionsFromWAF\Html */

defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=ExceptionsFromWAF">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_WAFEXCEPTIONS_EDIT');?>
</h1>

<section class="akeeba-panel">
    <form action="<?php echo ADMINTOOLSWP_URL; ?>&view=ExceptionsFromWAF" method="post" class="akeeba-form--horizontal">
        <div class="akeeba-container--66-33">
            <div>
                <div class="akeeba-form-group">
                    <label for="descr"><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_DESCR'); ?></label>

                    <input type="text" name="descr" id="descr" value="<?php echo isset($this->item) ? $this->escape($this->item->descr) : ''; ?>" />
                </div>

                <div class="akeeba-form-group">
                    <label for="at_url"><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_URL'); ?></label>

                    <input type="text" name="at_url" id="at_url" value="<?php echo isset($this->item) ? $this->escape($this->item->at_url) : ''; ?>" />
                    <p>
                        <?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_URL_TIP')?>
                    </p>
                </div>

                <div class="akeeba-form-group">
                    <label for="at_type"><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_TYPE') ?></label>

                    <?php
                        $checked_1 = '';
                        $checked_2 = 'checked';

                        if (isset($this->item) && $this->item->at_type == 'regex')
                        {
							$checked_1 = 'checked';
							$checked_2 = '';
                        }
                    ?>

                    <div class="akeeba-toggle">
                        <input type="radio" name="at_type" <?php echo $checked_2 ?> id="at_type-2" value="exact">
                        <label for="at_type-2"> <?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_EXACT') ?></label>
                        <input type="radio" name="at_type" <?php echo $checked_1 ?> id="at_type-1" value="regex">
                        <label for="at_type-1" class="grey"><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_REGEX') ?></label>
                    </div>
                </div>

                <div class="akeeba-form-group">
                    <label for="at_param"><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_PARAM'); ?></label>

                    <input type="text" name="at_param" id="at_param" value="<?php echo isset($this->item) ? $this->escape($this->item->at_param) : ''; ?>" />
                </div>

                <div class="akeeba-form-group">
                    <label for="at_value"><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_VALUE'); ?></label>

                    <input type="text" name="at_value" id="at_value" value="<?php echo isset($this->item) ? $this->escape($this->item->at_value) : ''; ?>" />
                </div>

                <div class="akeeba-form-group">
                    <label for="published"><?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_PUBLISHED')?></label>

                    <?php echo SelectHelper::booleanswitch('published', isset($this->item) ? $this->item->published : 1)?>
                </div>
            </div>
        </div>

        <p>
            <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
            <a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=ExceptionsFromWAF">
				<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
            </a>
        </p>

        <div class="akeeba-hidden-fields-container">
            <input type="hidden" name="view" value="ExceptionsFromWAF" />
            <input type="hidden" name="task" value="save" />
            <input type="hidden" name="id" id="id" value="<?php echo isset($this->item) ? (int)$this->item->id : ''; ?>" />
			<?php wp_nonce_field('postExceptionsFromWAF') ?>
        </div>
    </form>
</section>