<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\SecurityExceptions\Html */

use Akeeba\AdminTools\Admin\Helper\Html;
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Admin\Helper\Storage;

defined('ADMINTOOLSINC') or die;

$cparams = Storage::getInstance();
$iplink = $cparams->getValue('iplookupscheme', 'http') . '://' . $cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');
?>

<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_LOG');?>
</h1>

<form id="admintoolswpForm" method="get" class="akeeba-form">
    <input type="hidden" name="page" value="admintoolswp/admintoolswp.php" />
    <input type="hidden" name="view" value="SecurityExceptions">
    <?php wp_nonce_field('getSecurityExceptions', '_wpnonce', false) ?>

    <p class="search-box">
        <?php echo Select::reasons($this->input->getCmd('reason', ''))?>

        <input type="search" name="ip" value="<?php echo $this->escape($this->input->getString('ip', null))?>"
               placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_IP')?>"/>

        <input type="search" name="url" value="<?php echo $this->escape($this->input->getString('url', null))?>"
               placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_URL')?>"/>
        <input type="submit" id="search-submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SEARCH')?>">
    </p>

    <div class="tablenav top">
        <?php echo Html::bulkActions(array('delete'))?>
        <?php echo Html::pagination($this->total, $this->limitstart)?>
    </div>

    <table class="akeeba-table--striped">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-1" type="checkbox" />
                </td>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_LOGDATE'), 'logdate')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_IP'), 'ip')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON'), 'reason')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_URL'), 'url')?>
            </tr>
        </thead>

        <tbody>
            <?php if (!$this->items):?>
                <tr>
                    <td colspan="20"><?php echo Language::_('COM_ADMINTOOLS_MSG_COMMON_NOITEMS')?></td>
                </tr>
            <?php else:
				$cparams = Storage::getInstance();
				$iplink  = $cparams->getValue('iplookupscheme', 'http') . '://' . $cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');

                foreach($this->items as $item):
					$link = str_replace('{ip}', $item->ip, $iplink);

					$ip = '<a href="'.$link.'" target="_blank" class="akeeba-btn--small"><span class="akion-search"></span></a>&nbsp;';

					$token = wp_create_nonce('getSecurityExceptions');

					if($item->block)
					{
						$ip .= '<a class="akeeba-btn--green--small" ';
						$ip .= 'href="'.ADMINTOOLSWP_URL.'&view=SecurityExceptions&task=unban&id='.$item->id.'&_wpnonce='.$token.'" ';
						$ip .= 'title="'.Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_UNBAN').'">';
						$ip .= '<span class="akion-minus"></span>';
						$ip .= '</a>&nbsp;';
					}
					else
					{
						$ip .= '<a class="akeeba-btn--red--small" ';
						$ip .= 'href="'.ADMINTOOLSWP_URL.'&view=SecurityExceptions&task=ban&id='.$item->id.'&_wpnonce='.$token.'" ';
						$ip .= 'title="'.Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_BAN').'">';
						$ip .= '<span class="akion-flag"></span>';
						$ip .= '</a>&nbsp;';
					}

					$ip .= $this->escape($item->ip);

					$reason = Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_'.$item->reason);

	                if ($item->extradata)
	                {
		                if (stristr($item->extradata, '|') === false)
		                {
			                $item->extradata .= '|';
		                }

		                list($moreinfo, $techurl) = explode('|', $item->extradata);

		                $reason .= '&nbsp;<span class="akeeba-btn--primary-mini" style="padding:.2em" title="'.$moreinfo.'"><span class="akion-information-circled"></span></span>';
	                }
            ?>
                <tr>
                    <td class="check-column">
                        <input id="cb-select-<?php echo $item->id ?>" type="checkbox" name="cid[]" value="<?php echo $item->id?>" />
                    </td>
                    <td><?php echo $this->escape($item->logdate)?></td>
                    <td><?php echo $ip ?></td>
                    <td><?php echo $reason ?></td>
                    <td><?php echo $this->escape($item->url) ?></td>
                </tr>
            <?php endforeach;?>
            <?php endif;?>
        </tbody>

        <tfoot>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-2" type="checkbox" />
                </td>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_LOGDATE'), 'logdate')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_IP'), 'ip')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON'), 'reason')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_URL'), 'url')?>
            </tr>
        </tfoot>
    </table>
</form>
