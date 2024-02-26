<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Widget;

use Akeeba\AdminTools\Admin\Helper\Wordpress;

class Graphs
{
	public static function display()
	{
		if (!defined('ADMINTOOLSINC'))
		{
			echo "Admin Tools is not properly installed, or has been disabled.";

			return;
		}

		$network = is_multisite() ? 'network/' : '';
		$bootstrapUrl = admin_url() . $network . 'admin.php?page=' . \AdminToolsWP::$dirName . '/' . \AdminToolsWP::$fileName;

		wp_enqueue_script('chart.min.js', 'https://cdn.jsdelivr.net/npm/chart.js@3.2.1/dist/chart.min.js');
		wp_enqueue_script('moment@2.27.0', 'https://cdn.jsdelivr.net/npm/moment@2.27.0');
		wp_enqueue_script('chartjs-adapter-moment@0.1.1', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@0.1.1', [
			'chart.min.js',
			'moment@2.27.0'
		]);
		wp_enqueue_script('cpanelgraphs.js', ADMINTOOLSWP_MEDIAURL . '/app/media/js/cpanelgraphs.js', [
			'jquery-ui-datepicker'
		]);
		wp_add_inline_script('cpanelgraphs.js', <<< JS
admintools.ControlPanelGraphs.plugin_url = '{$bootstrapUrl}';
JS
		);
		wp_enqueue_style('jquery-ui-datepicker');

		include dirname(\AdminToolsWP::$absoluteFileName) . '/app/view/controlpanel/tmpl/graphs.php';
		?>
		<?php

	}
}