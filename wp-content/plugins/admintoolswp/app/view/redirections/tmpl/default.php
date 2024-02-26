<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var $this \Akeeba\AdminTools\Admin\View\Redirections\Html */

use Akeeba\AdminTools\Admin\Helper\Html;
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

defined('ADMINTOOLSINC') or die;

$model = $this->getModel();

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_REDIRS');?>
</h1>

	<div class="akeeba-panel--info">
		<form name="enableForm" action="<?php echo ADMINTOOLSWP_URL; ?>&view=Redirections" method="post" class="akeeba-form--inline">
			<div class="akeeba-form-group">
				<label for="urlredirection"><?php echo Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE'); ?></label>
				<?php echo SelectHelper::booleanswitch('urlredirection', $this->urlredirection) ?>
			</div>
			<div class="akeeba-form-group">
				<input class="akeeba-btn--dark" type="submit"
					   value="<?php echo Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE_SAVE') ?>"/>
			</div>

			<input type="hidden" name="view" id="view" value="Redirections"/>
			<input type="hidden" name="task" id="task" value="applypreference"/>
			<?php wp_nonce_field('postRedirections') ?>
		</form>
	</div>

    <form id="admintoolswpForm" method="get" class="akeeba-form">
        <p class="search-box">
			<input type="search" name="dest" value="<?php echo $this->escape($this->input->getString('dest', null))?>"
				   placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_DEST')?>"/>

            <input type="search" name="source" value="<?php echo $this->escape($this->input->getString('source', null))?>"
                   placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE')?>"/>

            <?php echo Select::published($this->input->getCmd('published', ''), 'published')?>

            <input type="submit" id="search-submit" class="button" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SEARCH')?>">
        </p>

        <div class="tablenav top">
			<?php echo Html::bulkActions(array('copy', 'delete'))?>

            <a class="akeeba-btn--green" href="<?php echo esc_url(ADMINTOOLSWP_URL.'&view=Redirections&task=add')?>">
				<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_ADD')?>
            </a>

			<?php echo Html::pagination($this->total, $this->limitstart)?>
        </div>

        <table class="akeeba-table--striped">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-1" type="checkbox" />
                </td>
				<?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_DEST'), 'dest')?>
				<?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE'), 'source')?>
				<?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_KEEPURLPARAMS'), 'keepurlparams')?>
				<?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_COMMON_PUBLISHED'), 'published')?>
            </tr>
            </thead>

            <tbody>
			<?php if (!$this->items):?>
                <tr>
                    <td colspan="20"><?php echo Language::_('COM_ADMINTOOLS_MSG_COMMON_NOITEMS')?></td>
                </tr>
			<?php else: ?>
				<?php foreach($this->items as $item):?>
                    <tr>
                        <td class="check-column">
                            <input id="cb-select-<?php echo $item->id ?>" type="checkbox" name="cid[]" value="<?php echo $item->id?>" />
                        </td>
						<td>
							<a href="<?php echo ADMINTOOLSWP_URL; ?>&view=Redirections&task=edit&id=<?php echo $item->id?>">
								<?= site_url() ?>/<strong><?= $this->escape($item->dest) ?></strong>
							</a>
						</td>
                        <td>
							<a href="<?= $this->escape(strstr($item->source, '://') ? $item->source : '../' . $item->source) ?>" target="_blank">
								<?= $this->escape($item->source) ?>&nbsp;<img src="<?=ADMINTOOLSWP_MEDIAURL?>/app/media/images/external-icon.gif" border="0"  alt=""/>
							</a>
                        </td>
                        <td>
                        <?php
                        switch ($item->keepurlparams)
                        {
	                        case 1:
		                        $key = 'ALL';
		                        break;

	                        case 2:
		                        $key = 'ADD';
		                        break;

	                        case 0:
	                        default:
		                        $key = 'OFF';
		                        break;
                        }

                            echo Language::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_' . $key);
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
	            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_DEST'), 'dest')?>
	            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE'), 'source')?>
	            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_KEEPURLPARAMS'), 'keepurlparams')?>
	            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_COMMON_PUBLISHED'), 'published')?>
            </tr>
            </tfoot>
        </table>

        <input type="hidden" name="page" value="admintoolswp/admintoolswp.php" />
        <input type="hidden" name="view" value="Redirections">
		<?php wp_nonce_field('getRedirections', '_wpnonce', false) ?>
    </form>

</div>
