<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var  Akeeba\AdminTools\Admin\View\ControlPanel\Html $this */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Model\ControlPanel;

// Protect from unauthorized access
defined('ADMINTOOLSINC') or die();

// IP Workarounds are available on Pro version only
if (!defined('ADMINTOOLSWP_PRO') || !ADMINTOOLSWP_PRO)
{
	return;
}

// Let's check if we have to display the notice about IP Workarounds
$display = false;
// Prevent notices if we don't have any incoming return url
$returnurl = isset($returnurl) ? $returnurl : '';

/** @var ControlPanel $controlPanelModel */
$controlPanelModel = $this->getModel();
$privateNetworks   = $controlPanelModel->needsIpWorkaroundsForPrivNetwork();
//$proxyHeader       = $controlPanelModel->needsIpWorkaroundsHeaders();
$proxyHeader       = false;

$display = ($privateNetworks || $proxyHeader);

// No notices detected, let's stop here
if (!$display)
{
    return;
}

?>

<div class="akeeba-block--failure">
    <?php if ($privateNetworks): ?>
        <p>
            <?php echo Language::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_IPS')?>
        </p>
    <?php endif; ?>

    <?php if($proxyHeader): ?>
        <p>
		    <?php echo Language::_('COM_ADMINTOOLS_CPANEL_ERR_PROXY_HEADER')?>
        </p>
    <?php endif; ?>
	<a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ControlPanel&task=IpWorkarounds&enable=1&returnurl=<?php echo $returnurl?>" class="akeeba-btn--green">
		<?php echo Language::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_ENABLE')?>
	</a>
	<a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ControlPanel&task=IpWorkarounds&enable=0&returnurl=<?php echo $returnurl?>" class="akeeba-btn--dark">
		<?php echo Language::_('COM_ADMINTOOLS_CPANEL_ERR_PRIVNET_IGNORE')?>
	</a>
</div>
