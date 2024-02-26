<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\MasterPassword\Html */

// Protect from unauthorized access
defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

?>
<div>
    <h1><a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>"><span class="akion-chevron-left"></span><span class="aklogo-admintools-wp-small"></span> <?php echo Language::_('COM_ADMINTOOLS').'</a>'.Language::_('COM_ADMINTOOLS_TITLE_MASTERPW');
        ?>
    </h1>

    <form action="<?php echo ADMINTOOLSWP_URL; ?>&view=MasterPassword" method="post" name="adminForm" id="adminForm" class="akeeba-form">
        <div class="akeeba-panel--primary">
            <header class="akeeba-block-header">
                <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PASSWORD'); ?></h3>
            </header>
            <div class="akeeba-form-section--horizontal">
                <div class="akeeba-form-group">
                    <label for="masterpw"><?php echo Language::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PWPROMPT'); ?></label>
                    <div><input id="masterpw" type="password" name="masterpw" value="<?php echo $this->escape($this->masterpw); ?>"/></div>
                </div>
            </div>
        </div>

        <div class="akeeba-panel--default">
            <header class="akeeba-block-header">
                <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PROTVIEWS'); ?></h3>
            </header>

            <div class="akeeba-form-section--horizontal">
                <div class="akeeba-form-group">
                    <p><?php echo Language::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_QUICKSELECT'); ?></p>

                    <div class="akeeba-form-actions">
                        <button class="akeeba-btn"
                                onclick="jQuery(jQuery('.radio-yes').get().reverse()).click();return false;"><?php echo Language::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_ALL'); ?></button>
                        <button class="akeeba-btn"
                                onclick="jQuery(jQuery('.radio-no').get().reverse()).click();return false;"><?php echo Language::_('COM_ADMINTOOLS_LBL_MASTERPASSWORD_NONE'); ?></button>
                    </div>
                </div>
                <?php foreach ($this->items as $view => $x):
                    list($locked, $langKey) = $x;
                    ?>
                    <div class="akeeba-form-group">
                        <label for="views[<?php echo $this->escape($view); ?>]"
                               class="control-label"><?php echo Language::_($langKey); ?></label>
                        <div><?php echo SelectHelper::booleanswitch('views[' . $view . ']', ($locked ? 1 : 0),  array('class' => 'masterpwcheckbox')); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>


            <div class="akeeba-form-actions">
                <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
                <a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>">Cancel</a>
            </div>
        </div>

        <input type="hidden" name="view" value="MasterPassword"/>
        <input type="hidden" name="task" value="save"/>
		<?php wp_nonce_field('postMasterPassword')?>
    </form>
</div>
