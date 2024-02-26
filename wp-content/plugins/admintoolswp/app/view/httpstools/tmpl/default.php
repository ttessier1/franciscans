<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\HttpsTools\Html */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

defined('ADMINTOOLSINC') or die;

?>

<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
        <?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
    <?php echo Language::_('COM_ADMINTOOLS_TITLE_HTTPSTOOLS'); ?>
</h1>

<section class="akeeba-panel">
    <form action="<?php echo ADMINTOOLSWP_URL; ?>&view=HttpsTools" method="post" class="akeeba-form--horizontal">
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_LBL_HTTPSTOOLS_OPT_HTTPSIZER'); ?></label>
            <div>
				<?php echo SelectHelper::booleanswitch('httpsizer', $this->salconfig['httpsizer']); ?>
                <p class="akeeba-help-text">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_HTTPSTOOLS_OPT_HTTPSIZER_DESC'); ?>
                </p>
            </div>
        </div>

        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_LBL_HTTPSTOOLS_OPT_FORCEWPHTTPS'); ?></label>
            <div>
				<?php echo Select::forcehttps('forcewphttps', array(), $this->salconfig['forcewphttps']); ?>
                <p class="akeeba-help-text">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_HTTPSTOOLS_OPT_FORCEWPHTTPS_DESC')?>
                </p>
            </div>
        </div>

        <p class="submit">
            <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
            <a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=ControlPanel">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
            </a>
        </p>

        <input type="hidden" name="view" value="HttpsTools"/>
        <input type="hidden" name="task" value="save"/>
		<?php wp_nonce_field('postHttpsTools') ?>
    </form>
</section>
