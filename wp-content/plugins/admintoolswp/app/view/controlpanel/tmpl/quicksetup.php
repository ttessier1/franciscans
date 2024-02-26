<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Uri\Uri;

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */

defined('ADMINTOOLSINC') or die;

$uriBase = rtrim(Uri::base(), '/');

?>
<div class="akeeba-panel--default">
    <header class="akeeba-block-header">
        <h3><?php echo Language::_('COM_ADMINTOOLS_CONTROLPANEL_HEADER_QUICKSETUP'); ?></h3>
    </header>

    <div class="akeeba-block--warning">
        <?php echo Language::_('COM_ADMINTOOLS_CONTROLPANEL_HEADER_QUICKSETUP_HELP'); ?>
    </div>

    <div class="akeeba-grid">
        <div>
            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=QuickStart" class="akeeba-action--orange">
                <span class="akion-flash"></span>
                <?php echo Language::_('COM_ADMINTOOLS_TITLE_QUICKSTART'); ?><br/>
            </a>
        </div>
    </div>
</div>
