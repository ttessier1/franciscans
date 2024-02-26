<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */
use Akeeba\AdminTools\Admin\Helper\Language;

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this */

// Protect from unauthorized access
defined('ADMINTOOLSINC') or die;

$graphDayFrom = gmdate('Y-m-d', time() - 30 * 24 * 3600);
?>
<div class="akeeba-panel--default">
    <header class="akeeba-block-header">
        <h3><?php echo Language::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_EXCEPTIONS'); ?></h3>
    </header>

    <form class="akeeba-form--inline">
        <div class="akeeba-form-group">
            <label for="admintools_graph_datepicker"><?php echo Language::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_FROMDATE'); ?></label>
            <input type="date" class="form-control" name="admintools_graph_datepicker" id="admintools_graph_datepicker" value="<?php echo $graphDayFrom?>" />

            <button class="akeeba-btn--dark--small" id="admintools_graph_reload" onclick="return false;">
				<?php echo Language::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_RELOADGRAPHS'); ?>
            </button>
        </div>
    </form>

    <div class="akeeba-graph">
        <p class="text-center"><img src="<?php echo ADMINTOOLSWP_MEDIAURL.'/app/media/images/throbber.gif'; ?>" id="akthrobber"/></p>
        <canvas id="admintoolsExceptionsLineChart" width="400" height="200"></canvas>

        <div id="admintoolsExceptionsLineChartNoData" style="display:none" class="akeeba-block--success small">
            <p><?php echo Language::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_NODATA'); ?></p>
        </div>
    </div>

    <div class="clearfix"></div>

    <h4><?php echo Language::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_EXCEPTSTATS'); ?></h4>
    <div class="akeeba-graph">
        <p class="text-center"><img src="<?php echo ADMINTOOLSWP_MEDIAURL.'/app/media/images/throbber.gif'; ?>" id="akthrobber2"/></p>
        <canvas id="admintoolsExceptionsPieChart" width="400" height="200"></canvas>

        <div id="admintoolsExceptionsPieChartNoData" style="display:none" class="akeeba-block--success small">
            <p><?php echo Language::_('COM_ADMINTOOLS_CONTROLPANEL_DASHBOARD_STATS_NODATA'); ?></p>
        </div>
    </div>
</div>
