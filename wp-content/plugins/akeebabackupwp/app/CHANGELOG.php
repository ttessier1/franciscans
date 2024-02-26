<?php die();?>
Akeeba Solo 8.2.0
================================================================================
! Cannot complete the setup due to an inversion of login in the Setup view
+ Expert options for the Upload to Amazon S3 configuration
+ Separate remote and local quota settings
# [MEDIUM] Clicking on Backup Now would start the backup automatically

Akeeba Solo 8.1.2
================================================================================
+ Automatically downgrade utf8mb4_900_* collations to utf8mb4_unicode_520_ci on MariaDB
+ Joomla restoration: allows you to change the robots (search engine) option
~ Change the message when the PHP or WordPress requirements are not met in available updates
~ Remove the message about the release being 120 days old

Akeeba Solo 8.1.1
================================================================================
- Removed support for Akeeba Backup JSON API v1 (APIv1)
- Removed support for the legacy Akeeba Backup JSON API endpoint (wp-content/plugins/akeebabackupwp/app/index.php)
# [MEDIUM] PHP error when adding Solo to the backup

Akeeba Solo 8.1.0
================================================================================
# [HIGH] PHP error in Manage Backups when you have pending or failed backups

Akeeba Solo 8.1.0.b1
================================================================================
# [LOW] Downgrading from Pro to Core would make it so that you always saw an update available
# [LOW] Management column show the wrong file extension for the last file you need to download

Akeeba Solo 8.0.0
================================================================================
+ Minimum PHP version is now 7.4.0
+ Using Composer to load all internal dependencies (AWF, backup engine, S3 library)
+ Workaround for Wasabi S3v4 signatures
+ Support for uploading to Shared With Me folders in Google Drive
~ Improved error reporting, removing the unhelpful "(HTML containing script tags)" message
~ Improved mixed– and upper–case database prefix support at backup time
# [MEDIUM] Resetting corrupt backups can cause a crash of the Control Panel page
# [MEDIUM] Upload to S3 would always use v2 signatures with a custom endpoint.
# [MEDIUM] Some transients need data replacements to take place in WP 6.3
