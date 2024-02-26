<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Html;
use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\ExceptionsFromWAF\Html */

defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_WAFEXCEPTIONS');?>
</h1>

<form method="get" class="akeeba-form">
    <input type="hidden" name="page" value="admintoolswp/admintoolswp.php" />
    <input type="hidden" name="view" value="ExceptionsFromWAF" />
    <?php wp_nonce_field('getExceptionsFromWAF', '_wpnonce', false) ?>

    <p class="search-box">
        <input type="search" name="at_url" value="<?php echo $this->escape($this->input->getString('at_url', null))?>"
               placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_URL')?>"/>
        <input type="search" name="descr" value="<?php echo $this->escape($this->input->getString('descr', null))?>"
               placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_DESCR')?>"/>

        <input type="submit" id="search-submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SEARCH')?>">
    </p>

    <div class="tablenav top">
        <?php echo Html::bulkActions(array('delete'))?>

        <a class="akeeba-btn--green" href="<?php echo esc_url(ADMINTOOLSWP_URL.'&view=ExceptionsFromWAF&task=add')?>">
			<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_ADD')?>
        </a>

        <?php echo Html::pagination($this->total, $this->limitstart)?>
    </div>

    <table class="akeeba-table--striped">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column" style="width:40px;">
                    <input id="cb-select-all-1" type="checkbox" />
                </td>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_URL'), 'at_url')?>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_DESCR')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_PARAM')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_VALUE')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_TYPE') ?></td>
				<?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_COMMON_PUBLISHED'), 'published', 'width:100px')?>
            </tr>
        </thead>

        <tbody>
        <?php if (!$this->items):?>
            <tr>
                <td colspan="20"><?php echo Language::_('COM_ADMINTOOLS_MSG_COMMON_NOITEMS')?></td>
            </tr>
        <?php else: ?>
            <?php
                foreach($this->items as $item):
                    $link = ADMINTOOLSWP_URL.'&view=ExceptionsFromWAF&task=edit&id='.$item->id;
            ?>
                <tr>
                    <td class="check-column">
                        <input id="cb-select-<?php echo $item->id ?>" type="checkbox" name="cid[]" value="<?php echo $item->id?>" />
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $item->at_url ?>
                        </a>
                    </td>
                    <td><?php echo $item->descr ?></td>
                    <td><?php echo $item->at_param ?></td>
                    <td><?php echo $this->escape($item->at_value) ?></td>
                    <td>
						<?php
						$type = $item->at_type == 'regex' ? Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_REGEX') : Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_EXACT');
						echo $type;
						?>
                    </td>
                    <td>
                    <?php
                        $pubIcon = '<span class="akeeba-label--green"><span class="akion-checkmark"></span></span>';

                        if (!$item->published)
                        {
                            $pubIcon = '<span class="akeeba-label--red"><span class="akion-close"></span></span>';
                        }
                        echo $pubIcon;
                    ?>
                    </td>
                </tr>
            <?php endforeach;?>
        <?php endif;?>
        </tbody>

        <tfoot>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-2" type="checkbox" />
                </td>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_URL'), 'at_url')?>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_DESCR')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_TYPE') ?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_PARAM')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_WAFEXCEPTIONS_VALUE')?></td>
				<?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_COMMON_PUBLISHED'), 'published', 'width:100px')?>
            </tr>
        </tfoot>
    </table>
</form>
