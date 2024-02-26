<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\Salts\Html */

defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
        <span class="akion-chevron-left"></span><span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS').'</a>'.Language::_('COM_ADMINTOOLS_TITLE_SALTS'); ?>
</h1>

<section class="akeeba-panel">
    <form action="<?php echo ADMINTOOLSWP_URL; ?>&view=Salts" name="adminForm" id="adminForm" method="post" class="form form-horizontal">
        <p><?php echo Language::_('COM_ADMINTOOLS_SALTS_DESCR_1')?></p>
        <p><?php echo Language::_('COM_ADMINTOOLS_SALTS_DESCR_2')?></p>

        <p class="akeeba-block--warning"><?php echo Language::_('COM_ADMINTOOLS_SALTS_WARN')?></p>

        <p>
            <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_TITLE_SALTS')?>"/>
            <a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WPTools"><?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?></a>
        </p>

        <input type="hidden" name="view" value="Salts"/>
        <input type="hidden" name="task" id="task" value="change"/>
		<?php wp_nonce_field('postSalts') ?>
    </form>
</section>
