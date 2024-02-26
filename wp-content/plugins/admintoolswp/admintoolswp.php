<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/*
Plugin Name: Admin Tools Core for WordPress
Plugin URI: https://www.akeeba.com
Description: The complete security solution for WordPress
Version: 1.6.4
Author: Akeeba Ltd
Author URI: https://www.akeeba.com
Network: true
License: GPLv3
*/

// Make sure we are being called from WordPress itself
defined('WPINC') or die;
defined('ADMINTOOLSINC') or define('ADMINTOOLSINC', true);

// Preload our helper classes
require_once dirname(__FILE__) . '/helpers/admintoolswp.php';
require_once dirname(__FILE__) . '/helpers/admintoolswpupdater.php';

AdminToolsWP::initialization(__FILE__);

include_once __DIR__ . '/helpers/bootstrap.php';

// Register install/uninstall hooks
register_activation_hook(__FILE__, ['AdminToolsWP', 'install']);
register_deactivation_hook(__FILE__, ['AdminToolsWP', 'deactivate']);
register_uninstall_hook(__FILE__, ['AdminToolsWP', 'uninstall']);

// Register update hooks
add_filter('pre_set_site_transient_update_plugins', ['AdminToolsWPUpdater', 'getUpdateInformation'], 10, 2);
add_filter('plugins_api', ['AdminToolsWPUpdater', 'pluginInformationPage'], 10, 3);
add_filter('upgrader_pre_download', ['AdminToolsWPUpdater', 'addDownloadID'], 10, 3);
add_action('upgrader_process_complete', ['AdminToolsWPUpdater', 'postUpdate'], 10, 2);
add_filter('after_plugin_row_admintoolswp/admintoolswp.php', ['AdminToolsWPUpdater', 'updateMessage'], 10, 3);

// Register CRON hooks
add_action('admintoolswp_cron', ['AdminToolsWP', 'cron']);
add_action('admintoolswp_cron_hourly', ['AdminToolsWP', 'cronHourly']);
add_filter('cron_schedules', ['AdminToolsWP', 'cron_interval']);

// Register administrator plugin hooks
if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX))
{
	add_action('admin_menu', ['AdminToolsWP', 'adminMenu']);
	add_action('network_admin_menu', ['AdminToolsWP', 'networkAdminMenu']);
	add_filter('set-screen-option', ['AdminToolsWP', 'set_option'], 10, 3);

	// Required to strip slashes
	add_action('plugins_loaded', ['AdminToolsWP', 'fakeRequest'], 1);

	// Required to enable output buffering in Admin area
	add_action('init', ['AdminToolsWP', 'startAdminBuffer'], 2);

	// Add a hook to register dashboard widgets
	add_action('wp_dashboard_setup', ['AdminToolsWP', 'registerDashboardWidgets']);
}
