<?php die(); ?>
Akeeba Backup 8.2.0
================================================================================
+ Expert options for the Upload to Amazon S3 configuration
+ Separate remote and local quota settings
# [MEDIUM] Clicking on Backup Now would start the backup automatically
# [MEDIUM] CLI backups would not send emails, reporting the mysqli connection is already closed

Akeeba Backup 8.1.2
================================================================================
+ Automatically downgrade utf8mb4_900_* collations to utf8mb4_unicode_520_ci on MariaDB
~ Remove the message about the release being 120 days old

Akeeba Backup 8.1.1
================================================================================
- Removed support for Akeeba Backup JSON API v1 (APIv1)
+ Re-enabled integrated updates with WordPress
# [HIGH] Raw views include WordPress HTML
# [MEDIUM] SQL error after finishing migrating archives
# [LOW] Double URL in the JSON API section of the scheduling info page

Akeeba Backup 8.1.0
================================================================================
# [HIGH] PHP error in Manage Backups when you have pending or failed backups
# [HIGH] CLI scripts did not work through the 8.1.0 betas

Akeeba Backup 8.1.0.b3
================================================================================
# [HIGH] Wrong update URL

Akeeba Backup 8.1.0.b2
================================================================================
# [HIGH] The migration is never over if you have backup records claiming their archive files exist but, in fact, they do not

Akeeba Backup 8.1.0.b1
================================================================================
~ Moved the default backup output folder to wp-content/backups
~ Moved the settings encryption key to wp-content/akeebabackupwp_secretkey.php
~ Automatic migration of backup archives and backup profiles outside the plugin's root folder
~ Path shown for backups is now relative to WordPress' root folder (as reported by its `ABSPATH` constant)
- Removed admin dashboard widgets
# [LOW] Downgrading from Pro to Core would make it so that you always saw an update available
# [LOW] Management column show the wrong file extension for the last file you need to download

Akeeba Backup 8.0.0.2
================================================================================
# [HIGH] Change in WordPress itself causes a PHP fatal error at the end of the update
# [HIGH] Fatal error sending emails at the end of the backup
# [LOW] Cosmetic issue: application name appeared as Akeeba Solo instead of Akeeba Backup for WordPress in some screens
# [LOW] Translations not loaded during frontend and remote JSON API backups

Akeeba Backup 8.0.0.1
================================================================================
# [HIGH] Profile encryption key migration does not work when using WordPress' plugins update
# [LOW] PHP deprecated warnings

Akeeba Backup 8.0.0
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
# [HIGH] Not choosing a forced backup timezone in System Configuration results in the WP-CRON Scheduling page throwing an error