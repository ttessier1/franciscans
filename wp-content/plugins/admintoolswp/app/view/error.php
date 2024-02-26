<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var Exception|Throwable $e */

$code     = $e->getCode();
$code     = empty($code) ? '' : ((int) $code);
$file     = str_replace(realpath(ABSPATH), '&lt;WordPress Root&gt;', $e->getFile());
$memLimit = function_exists('ini_get') ? ini_get('memory_limit') : '(Unknown)';
$memUsage = memory_get_usage();
$maxMemUsage = memory_get_peak_usage();

global $wp_db_version, $wpdb;

?>
<h1>Admin Tools – Internal Application Error</h1>
<p>
	Admin Tools has stopped responding due to an unhandled internal application error.
</p>
<p>
	If you are a subscriber and need to request support please include all of the following information in your support
	request. Thank you!
</p>
<h2>
	<?php if (!empty($code)): ?>
		<code><?= $code ?></code>
	<?php endif; ?>
	<?= $e->getMessage() ?>
</h2>
<table>
	<tr>
		<th>Exception type</th>
		<td>
			<?= get_class($e) ?>
		</td>
	</tr>
	<tr>
		<th>File and line</th>
		<td>
			<?= $file ?> (<?= $e->getLine() ?>)
		</td>
	</tr>
</table>
<h3>Debug backtrace</h3>
<pre><?= $e->getTraceAsString() ?></pre>
<h3>System information</h3>
<table>
	<tr>
		<th>Admin Tools Version</th>
		<td>
			<?= ADMINTOOLSWP_VERSION ?>
			(released <?= ADMINTOOLSWP_DATE ?>)
		</td>
	</tr>
	<tr>
		<th>PHP Version</th>
		<td>
			<?= PHP_VERSION ?>
		</td>
	</tr>
	<tr>
		<th>WordPress Version</th>
		<td>
			<?= get_bloginfo( 'version' ); ?>
		</td>
	</tr>
	<tr>
		<th>Database</th>
		<td>
			<?= $wpdb->is_mysql ? 'MySQL' : 'Non-MySQL' ?>
			<?= $wpdb->db_version() ?>
		</td>
	</tr>
	<tr>
		<th>WordPress Multisite</th>
		<td>
			<?= is_multisite() ? 'Yes' : 'No' ?>
		</td>
	</tr>
	<tr>
		<th>Operating System</th>
		<td>
			<?= PHP_OS ?>
		</td>
	</tr>
	<tr>
		<th>Memory limit</th>
		<td>
			<?= $memLimit ?>
		</td>
	</tr>
	<tr>
		<th>Memory usage</th>
		<td>
			<?= number_format($memUsage, 0, '.', ',') ?> bytes
		</td>
	</tr>
	<tr>
		<th>Maximum memory usage</th>
		<td>
			<?= number_format($maxMemUsage, 0, '.', ',') ?> bytes
		</td>
	</tr>
</table>