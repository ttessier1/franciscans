<?php die();?>
Akeeba Solo 7.3.2
================================================================================
# [HIGH] CLI and remote backups could end up running forever if the MySQL connection was closed by the host at an inopportune moment
# [MEDIUM] Remote backup failure on hosts which prevent creation of .php files

Akeeba Solo 7.3.1
================================================================================
~ Normalized the default backup description under all backup methods (backend, frontend, CLI, JSON API, WP-CLI)
+ Massive speedup in data replacement of heavily nested serialised tables with thousands of elements
+ You can select a faster algorithm for data replacement of really big serialised data during restoration
# [HIGH] WebDAV fails to upload because of the wrong absolute URL being calculated
# [LOW] pCloud was erroneously listed in the free of charge Core version (it requires a paid subscription and was thus unusable)
# [LOW] Frontend backup URL does not work if the secret key contains the plus sign (+) character due to a PHP bug.

Akeeba Solo 7.3.0
================================================================================
+ S3: Add support for Cape Town and Milan regions
+ You can now change the base font size in Akeeba Solo
+ Added feature to "freeze" some backup records to keep them indefinitely
- Removed support for Internet Explorer
~ Improve default header and body fonts for similar cross-platform "feel" without the need to use custom fonts.
~ Rendering improvements
~ Adjust size of control panel icons
# [HIGH] Replacing (not just removing) AddHandler/SetHandler lines would fail during restoration
# [MEDIUM] Fetching back to server the archives from these provides would result in invalid archives: Amazon S3, Backblaze, Cloudfiles, OVH, Swift
# [MEDIUM] Greedy RegEx match in database dump could mess up views containing the literal ' view ' (word "view" surrounded by spaces) in their definition.
# [LOW] The akeeba_common table is not automatically excluded when you choose not to include Solo in the backup
# [LOW] Fixed fatal error when trying to use a non-existent profile
# [LOW] Fixed filtering by Profile in Manage Backups view
# [LOW] Fixed timestamp in default backup description

Akeeba Solo 7.2.2.1
================================================================================
# [MEDIUM] The warning about the default directory being in use was not visible on the Control Panel page

Akeeba Solo 7.2.2
================================================================================
+ Automatic UTF8MB4 character encoding downgrades from MySQL 8 to 5.7/5.6/5.5 on restoration.
# [MEDIUM] Missing declaration for cacert.pem path

Akeeba Solo 7.2.0.1
================================================================================
! The version file was missing from the package, causing the update to always show up as being available

Akeeba Solo 7.2.0
================================================================================
+ Minimum required PHP version is now 7.1.0
~ Remove multiple, unnecessary copies of the cacert.pem file
# [LOW] Very rare backup failures with a JS error

Akeeba Solo 7.1.4
================================================================================
# [LOW] Multipart upload to BackBlaze B2 might fail due to a silent B2 behavior change
# [LOW] OneDrive upload failure if a part upload starts >3600s after token issuance

Akeeba Solo 7.1.3
================================================================================
~ Reserved version number to maintain continuity with Akeeba Backup for Joomla versioning

Akeeba Solo 7.1.2
================================================================================
+ Improved error handling allows reporting PHP fatal errors (only available on sites using PHP 7)
# [LOW] Fixed typos that could create issues with servers using very restrictive security rules
# [LOW] (S)FTP connection test would report "false" instead of the reason of failure
# [LOW] Fixed archive download using the browser under some circustances

Akeeba Solo 7.1.1
================================================================================
~ Possible exception when the user has erroneously put their backup output directory to the site's root with open_basedir restrictions restricting access to its parent folder.
# [MEDIUM] OneDrive for Business is not working at all in Akeeba Solo

Akeeba Solo 7.1.0
================================================================================
+ Automatic security check of the backup output directory
+ Option to change post GUIDs on restoration
+ Yes/No toggles are now colorful instead of plain teal
~ Renamed helper functions
~ Improved storage of temporary data during backup [akeeba/engine#114]
~ Log files now have a .php extension to prevent unauthorized access in very rare cases
~ Enforce the recommended, sensible security measures when using the default backup output directory
~ Ongoing JavaScript refactoring
~ Google Drive: fetch up to 100 shared drives (previously: up to 10)
# [MEDIUM] CloudFiles post-processing engine: Fixed file uploads
# [MEDIUM] Swift post-processing engine: Fixed file uploads
# [LOW] Send by Email reported a successful email sent as a warning
# [LOW] Too many (removed and no longer working) options for site types in the Configuration Wizard.
# [LOW] Extra greater-than sign in the Configuration icon's URL in the Control Panel page
# [LOW] The tooltips visual style was slightly broken
# [LOW] Database dump: foreign keys' (constraints) and local indices' names did not get their prefix replaced like tables, views etc do

Akeeba Solo 7.0.2
================================================================================
~ Log the full path to the computed site's root, without <root> replacement
# [HIGH] Core (free of charge) version only: the PayPal donation link included a tracking pixel. Changed to donation link, without tracking.
# [MEDIUM] Integrated restoration: sanity checks were not applied, resulting in extraction errors
# [MEDIUM] WebDav post-processing engine: first backup archive was always uploaded on the remote root, ignoring any directory settings
# [HIGH] Restoration will fail if a table's name is a superset of another table's name e.g. foo_example_2020 being a superset of foo_example_2.

Akeeba Solo 7.0.1
================================================================================
- pCloud: removing download to browser (cannot work properly due to undocumented API restrictions)
# [HIGH] An error about not being able to open a file with an empty name occurs when taking a SQL-only backup but there's a row over 1MB big
# [LOW] Notice in Control Panel when maximum error reporting is enabled
# [LOW] Backup log file did not appear correctly (but you could still download it)
# [LOW] Redirections for the legacy frontend backup method should be to remote.php, not index.php
# [LOW] Dark Mode: modal close icon was invisible both in the backup software and during restoration
# [LOW] Dark Mode: some interface elements (e.g. footer, modal chrome) would always appear in their Light Mode scheme
# [LOW] Fixed automatically filling DropBox tokens after OAuth authentication

Akeeba Solo 7.0.0
================================================================================
+ Remove TABLESPACE and DATA|INDEX DIRECTORY table options during backup
# [MEDIUM] Fetching update information could result in an exception if the latest available version cannot be installed on this site
# [LOW] FTP and SFTP connection tests were always failing
# [LOW] Fixed applying quotas for obsolete backups

Akeeba Solo 7.0.0.rc1
================================================================================
+ Upload to OVH now supports Keystone v3 authentication, mandatory starting mid-January 2020
- Remove obsolete "Use IFRAMEs instead of AJAX" option
# [HIGH] An error in an early backup domain could result in a forever-running backup
# [HIGH] DB connection errors wouldn't result in the backup failing, as it should be doing
# [HIGH] Manage remotely stored files: Fetch to server would fail after the first batch of downloads

Akeeba Solo 7.0.0.b3
================================================================================
+ Common PHP version warning scripts
+ Reinstated support for pCloud after they fixed their OAuth2 server
~ Improved Dark Mode
~ Improved PHP 7.4 compatibility
~ Clearer message when setting decryption fails in CLI backup script
~ Replace JavaScript eval() with JSON.parse()
# [HIGH] The database dump was broken with some versions of PCRE (e.g. the one distributed with Ubuntu 18.04)
# [HIGH] The integrated restoration was broken

Akeeba Solo 7.0.0.b2
================================================================================
- Removed pCloud support
+ ANGIE: Options to remove AddHandler lines on restoration
# [MEDIUM] Fixed OAuth authentication flow
# [LOW] Configuration wizard will always prompt to the user

Akeeba Solo 7.0.0.b1
================================================================================
+ Amazon S3 now supports Bahrain and Stockholm regions
+ Amazon S3 now supports Intelligent Tiering, Glacier and Deep Archive storage classes
+ Google Storage now supports the nearline and coldline storage classes
+ Manage Backups: Improved performance of the Transfer (re-upload to remote storage) feature.
+ Windows Azure BLOB Storage: download back to server and download to browser are now supported
+ New OneDrive integration supports both regular OneDrive and OneDrive for Business
+ pCloud support
+ Support for Dropbox for Business
+ Minimum required PHP version is now 5.6.0
~ Common version numbering among all of our backup products means this version is 7, not 4
~ All views have been converted to Blade for easier development and better future-proofing
~ The integrated restoration feature is now only available in the Professional version
~ The front-end legacy backup API and the Remote JSON API are now available only in the Professional version
~ The Site Transfer Wizard is now only available in the Professional version
~ SugarSync integration: you now need to provide your own access keys following the documentation instructions
~ Backup error handling and reporting (to the log and to the interface) during backup has been improved.
~ The Test FTP/SFTP Connection buttons now return much more informative error messages.
~ Manage Backups: much more informative error messages if the Transfer to remote storage process fails.
~ The backup and log IDs will follow the numbering you see in the left hand column of the Manage Backups page.
~ Manage Backups: The Remote File Management page is now giving better, more accurate information.
~ Manage Backups: Fetch Back To Server was rewritten to gracefully deal with more problematic cases.
~ Removed AES encapsulations from the JSON API for security reasons. We recommend you always use HTTPS with the JSON API.
# [HIGH] Changing the database prefix would not change it in the referenced tables inside PROCEDUREs, FUNCTIONs and TRIGGERs
# [HIGH] Backing up PROCEDUREs, FUNCTIONs and TRIGGERs was broken
# [HIGH] Manage Backups: would not show Transfer Archive for qualifying backups not fully uploaded to remote storage.
# [MEDIUM] Database only backup of PROCEDUREs, FUNCTIONs and TRIGGERs does not output the necessary DELIMITER commands to allow direct import
# [MEDIUM] BackBlaze B2: upload error when chunk size is higher than the backup archive's file size
# [LOW] Manage Backups: the Remote Files Management dialog's size was off by several pixels
# [LOW] Manage Backups: downloading a part file from S3 beginning with text data would result in inline display of the file instead of download.
# [LOW] Disabled menu items (e.g. Backup Now) page confused people; removed them to prevent confusion