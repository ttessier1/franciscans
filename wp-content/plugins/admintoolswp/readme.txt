=== Admin Tools Core for WordPress ===
Contributors: nikosdion
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10903325
Tags: security, waf, performance
Requires at least: 4.9.0
Tested up to: 6.1
Requires PHP: 7.4
Stable tag: 1.6.4
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl.html

Protect your site against hackers and increase its performance.

== Description ==

Admin Tools for WordPress is an open-source security and performance optimization plugin for WordPress. Its mission is
simple: protect your site from hackers trying to break in while helping you optimize its performance.

Admin Tools includes an extensive set of tools and options to help you achieve these goals. Please remember that just
like all security tools it _does_ interfere with the operation of your site. Security is always at odds with
convenience. Blindly enabling all the options is virtually always going to cause problems to your site. Please read the
documentation to understand what each option does and how it impacts your site.

*Important note*: The limited Core version of the software and its
 [documentation](https://www.akeeba.com/documentation/atwp.html) are provided free of charge. Personalised support
 is not free; it requires paying for a support subscription. That's what pays the bills and lets us keep on writing good
 quality software full time.

Features:

* Administrator secret URL parameter. Protect your wp-admin login from brute force attacks.
* Password-protect WP administration. Protect the entire wp-admin folder against fuzzying and brute force attacks.
* Send an email for all administrator login attempts.
* Allow wp-admin access only to whitelisted IPs.
* Disable editing administrator user properties.
* Web Application Firewall for active protection of your site against hackers attacking WordPress.
* Automatically temporarily or pemanently block repeat attackers with customizable rules.
* Project Honeypot integration to keep hackers and spammers away.
* .htaccess Maker to tighten the security of your site at the web server level and optimize its performance.
* URL Redirection feature to turn your WordPress into a custom URL shortener or just redirect obsolete URLs from the old
  version of your site.

and much, much more!

Some of these features are only available in the Admin Tools Professional for WordPress which you can only download
after purchasing a [subscription](https://www.akeeba.com/subscribe/new/atwp.html?layout=default) on our site.

== Installation ==

1. Install Admin Tools for WordPress by uploading the files to your server. In the latter case we suggest you to upload
   the files into your site's `/wp-content/plugins/admintoolswp` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You will see the Admin Tools icon in your sidebar, below the Plugins section in the wp-admin area of your site.
   Click on it.
1. Click on the Quick Setup Wizard button to quickly apply a configuration to protect and optimize your site. Note down
   the troubleshooting URLs. You will need them in case your site becomes inaccessible after applying the new Admin
   Tools configuration.

== Frequently Asked Questions ==

= I have spotted a bug. Now what? =

Please use [our Contact Us page](https://www.akeeba.com/contact-us.html) to file a bug report. Make sure that you
indicate "Bug report" in the Category selection. We will review your bug report and work to fix it. We may contact you
for further information if necessary. If we don't contact you be assured that if you did report a bug we are already
working on fixing it.

= I am trying to install the plugin but the upload fails =

The plugin is quite big (around 4MB). Most servers have an upload limit of 2MB. You can either ask your host to increase
the file upload limit to 5MB or you can install the plugin manually. Please see the Installation section for more
information.

= I have a problem using the plugin. What should I do? =

The first thing you should do is [read our extensive documentation](https://www.akeeba.com/documentation/atwp.html)
and our [troubleshooter](https://www.akeeba.com/documentation/troubleshooter.html). If you'd like to receive
personalised support from the developers of the plugin you can [subscribe](https://www.akeeba.com/subscribe/new/atwp.html?layout=default)
to our services. Due to the very specialised nature of the software and our goal of providing exceptional support we do
not outsource our support. All support requests are answered by the developers who write the software. This is why we
require a subscription to provide support.

= Does your software support WordPress MU (multi-sites a.k.a. blog networks)? =

Not yet. We are working on it.

= WordPress moved to UTF8MB4 (UTF-8 Multibyte). Do you support it? =

Yes, we do.

= What are the requirements for your plugin? =

Admin Tools for WordPress requires PHP 7.4 or any later version. Older versions of PHP are not supported. We recommend using PHP 8.1 or later for security and performance reasons.

Admin Tools for WordPress has been tested on WordPress 6.0 and later. It should work on earlier versions of WordPress
but we cannot guarantee this. We recommend always using the latest version of WordPress for security and performance
reasons. There are some attacks which simply cannot be protected against through any web application firewall.

Admin Tools for WordPress requires at least 16MB of PHP memory (memory_limit). We strongly suggest 64MB or more for
optimal operation of the malware scanner on large sites.

Some features may require the PHP cURL extension to be installed and activated on your server. If unsure ask your host.

= Can I use this plugin on commercial sites / sites I am building for my clients? =

Yes, of course! Our plugin is licensed under the GNU General Public License version 3 or, at your option, any later
version of the license published by the Free Software Foundation. This license gives you the same Four Freedoms as
WordPress' license; in fact, GPLv3 is simply a newer version of the same GPLv2 license WordPress is using, one which
protects your interests even more.

= I have sites using other scripts / CMS. Can I use your software with them? =

Admin Tools for WordPress is designed explicitly to protect WordPress sites. You cannot use it with any other script or
CMS. We do have a version of Admin Tools for Joomla as well, available from our site.

== Screenshots ==

1. TODO

== Changelog ==

* min Tools for WordPress 1.6.4
* Missing escape in the security exceptions log

* min Tools for WordPress 1.6.3
* WordPress 6.3 breaks the update support in Admin Tools

* min Tools for WordPress 1.6.2
* .htaccess Maker: Case-insensitive match for allowed extensions
* .htaccess Maker: Improve HSTS support with an option which adds "includeSubDomains; preload" to the header
* .htaccess Maker: Use Header set instead of Header append for GZip and Brotli encoded files
* [HIGH] Empty “Force HTTPS for these URLs” will cause any HTTP access to redirect to the HTTPS site's root
* [HIGH] PHP error under PHP 8 in the Configure WAF page when you have blocked or allowed email domains
* [LOW] The admin widget appears in multisite network sites

* min Tools for WordPress 1.6.1
* Workaround for some sites reporting that the Language helper class is missing when it's not

* min Tools for WordPress 1.6.0
* Admin dashboard widget with Admin Tools' blocked requests graphs and stats
* You can now enable and disable plugin updates in WordPress' Plugins page anytime, not only when an update is available.
* Password Protect Administrator: improve compatibility with Apache 2.2 and 2.4
* [LOW] Cannot detect site's root in some cases when Optimize WAF is enabled
* [LOW] Wrong link to find the Download ID shown in the interface

* min Tools for WordPress 1.5.8
* Workaround for utf8_encode and _decode being deprecated in PHP 8.2
* PHP 8.1 compatibility improvements

* min Tools for WordPress 1.5.7
* [HIGH] CRON jobs would not work properly
* [LOW] Fixed notice while saving security exception to log file

* min Tools for WordPress 1.5.6
* [HIGH] Htaccess Maker: Changes in multi-line fields were not saved

* min Tools for WordPress 1.5.5
* [HIGH] PHP 8.1 throws an error running the Quick Setup Wizard because of a type mismatch in the .htaccess Maker settings

* min Tools for WordPress 1.5.4
* PHP 8.1 compatibility
* [HIGH] Quick Setup Wizard will come up with an error on PHP 8
* [HIGH] Fatal error in the Core version due to missing class
* [LOW] URL Redirections: Fixed pagination

* min Tools for WordPress 1.5.3
* [MEDIUM] UploadShield: Fixed fatal under PHP 8.0 when an empty file is uploaded
* [LOW] PHP notice in post-update code in very rare cases

* min Tools for WordPress 1.5.2
* [LOW] Prevent database errors in some rare conditions while sending out notification emails

* min Tools for WordPress 1.5.1
* [MEDIUM] .htaccess Maker, Disable client-side risky behavior in static content creates broken .htaccess file

* min Tools for WordPress 1.5.0
* Updated cacert.pem
* .htaccess Maker: Disable client-side risky behavior in static content

* min Tools for WordPress 1.4.2
* Update Chart.js
* Converted all tables to InnoDB for better performance
* Added feature to create Temporary Administrators
* [MEDIUM] WAF Email Templates were not correctly installed
* [MEDIUM] Fatal error uninstalling Admin Tools Core for WordPress
* [LOW] Warning in the cURL wrapper under PHP 8
* [LOW] Password Protect WP Administration: Fixed asking for access details while logging in or resetting the password

* min Tools for WordPress 1.4.1
* Releasing the previous version without changes, due to server and CDN issues which resulted in inconsistent updates
* Send out an email when an IP address is automatically banned
* When a custom Admin URL is used, now you can choose to issue a redirect to home page or show a 404 error page
* Improve the layout in the Unblock an IP page
* Improved CHANGELOG layout in the Control Panel page
* [LOW] Htaccess Maker: Blocking malicious user agents is now case insensitive

* min Tools for WordPress 1.4.0
* Send out an email when an IP address is automatically banned
* When a custom Admin URL is used, now you can choose to issue a redirect to home page or show a 404 error page
* Improve the layout in the Unblock an IP page
* Improved CHANGELOG layout in the Control Panel page
* [LOW] Htaccess Maker: Blocking malicious user agents is now case insensitive

* min Tools for WordPress 1.3.2
* Replace zero datetime with nullable datetime
* Add PHP 8.0 in the list of known PHP versions, recommend PHP 7.4 or later
* [MEDIUM] Security Exception graphs not showing at all
* [MEDIUM] Scheduling the Malware Scanner using a URL does not work since version 1.1.0

* min Tools for WordPress 1.3.1
* .htaccess Maker: Automatically compress static resources will now use Brotli compression with priority if it's supported by both the server (mod_brotli) and the client (accepts encoding "br").
* .htaccess Maker: Better support for more file types in setting the expiration time
* Improve the UX of the URL Redirect form page
* Adjust size of control panel icons
* Add .xsl to the default allowed extensions for .htaccess Maker's Site Protection feature
* [MEDIUM] Obsolete files were not deleted during installation/update

* min Tools for WordPress 1.3.0
* .htaccess/web.config Maker: more options for the expiration time
* .htaccess/web.config Maker: Static files compression now compresses the dynamic, WordPress-generated HTML for improved performance
* .htaccess/web.config Maker: Improved CORS handling
* .htaccess/web.config Maker: Improved Apache and IIS server signature removal
* Notify the user if a blocked IP belongs to an internal network, suggesting to enable IP workarounds
* Better unhandled error reporting in the administration interface
* Added option to disable image scaling
* Removed DFIShield feature since it was causing too many false positives
* [MEDIUM] Direct File Inclusion feature would trigger false positives on WordPress referer field

* min Tools for WordPress 1.2.1
* [MEDIUM] PHP File Change Scanner emails would fail due to wrong code imported since version 1.1.0
* [LOW] Log folder was not included in the package
* [LOW] Fixed PHP notice about missing constant

* min Tools for WordPress 1.2.0
* Added new options to tweak cookie paths and domains inside WordPress advanced configuration
* Added new options to tweak native CRON settings
* Added new options to tweak WordPress autoupdate policies for Core, Plugins, Themes and Translations
* The .htaccess Set a Long Expiration Time now also applies a no-cache setting for administrator URLs to prevent browsers from caching redirects and / or error messages in admin pages
* Add WebP to Set a Long Expiration Time in .htaccess and NginX Conf Maker
* Improved WAF Exceptions with better fine tuning
* Added support to render the Advanced Configuration page with tabs
* [LOW] Fixed displaying release notes when a new version comes out
* [LOW] Visual artifacts in some forms due to a missing CSS class
* [LOW] Some WAF features were not running during AJAX requests
* [LOW] URL Redirection: URL fragment included twice after redirecting to a URL that includes a fragment
* [LOW] PHP Scanner: Avoid compatibility issues with other plugins

* min Tools for WordPress 1.1.2
* Improved update system
* [HIGH] Links in translated messages were broken due to the wrong parsing of escaped double quotes
* [LOW] Remove custom section inside .htaccess file during uninstall
* [MEDIUM] Fetching update information could result in an exception if the latest available version cannot be installed on this site

* min Tools for WordPress 1.1.1
* [HIGH] Fixed fatal error during plugin activation

* min Tools for WordPress 1.1.0
* Removed GeoGraphic IP blocking due to changes in MaxMind's policy (see our site's News section)
* Password-protect WP Administration: option to reset custom error pages to avoid 404 errors accessing wp-admin
* Administrator IP whitelist, Never Block these IPs: you can now add dynamic IPv6 domain names instead of IPs by prefixing them with #.
* Common PHP version warning scripts
* Translations now use the INI format
* Away Schedule is now more clear about the use of time zones
* The Malware Scanner has been rewritten for better performance
* [LOW] Fixed harmless fatal error when Master Password was in use

* min Tools for WordPress 1.0.4
* Troubleshooting email sent automatically whenever your Admin Tools administrative action might lock you out of your site (gh-17)
* Added feature to manually unblock a specific IP (gh-72)
* Added option to allow only specific email domains during user registration (gh-74)

* min Tools for WordPress 1.0.3
* Added option to log usernames or not during failed logins (required by GDPR)
* [HIGH] IP filtering with CIDR or netmask notation may not work for certain IP address blocks
* [LOW] Fixed JavaScript error in Core version
* [LOW] Setup notice displayed in site notice instead of network admin on multisite installations
* [LOW] Fixed displaying extradata information inside the Security Exception Log

* min Tools for WordPress 1.0.2
* Display detected IP, country and continent inside the Geoblocking IP feature
* [HIGH] Broken WP plugins sending wrong parameters to the wp_login hook would cause the site to crash on user login
* [HIGH] The malware scanner does not work under CLI
* [MEDIUM] Disabling XML-RPC would result in a broken site when using the Optimized WAF
* [LOW] Quick Start fails in Core due to missing .htaccess Maker

* min Tools for WordPress 1.0.1
* Protection of all the plugin's folders against direct web access
* Updated default list of blocked User Agents
* Malware scanner: use WP's database connection constants when running inside WP
* Added PHP malware samples in the PHP File Change Scanner
* [HIGH] Plugin could not detect if it was enabled or not in multisite installations
* [HIGH] Cannot delete auto-banned IP addresses
* [LOW] Usage stats collection would not report the WordPress version correctly, leading to a PHP warning
* [LOW] Fixed fatal error if user never set a pagination limit

* min Tools for WordPress 1.0.0
* Official support for ClassicPress 1.x
* Show update status in Admin Tools' Control Panel page
* Option to disable XML-RPC services
* Mark All as Safe button in the Malware Scanner report viewer
* Language polishing
* Easier manual deactivation of the WAF using FTP or the hosting File Manager when you need to unblock yourself.
* Minimum update stability override when you are using a pre-release version for a better experience regarding bug fixes.
* [HIGH] Site protection made restoring a backup of the site taken with Akeeba Backup impossible.
* [HIGH] IP helper returns the IPv4-embedded-in-IPv6 address (::ffff:192.168.0.1 or 0:0:0:0:0:fff:192.168.0.1) instead of the unwrapped IPv4 address, making IP matching impossible.
* [HIGH] Infinite recursion when using the Custom Admin Folder feature when coming back to your site after your session expires aid if you have used the Remember Me feature.
* [LOW] The default .htaccess Maker exceptions for Akeeba Backup for WordPress were wrong
* [LOW] Notice in the Control Panel under PHP 7.3
* [LOW] Misleading information about PHP or WordPress version mismatch in update when the problem is the minimum stability
* [LOW] Warning thrown from the stats collection on WordPress 5.0 because this version number lacks a revision (5.0 vs 5.0.0)
* [LOW] Malware Detection page: Possible Threats column included "Marked as Safe" files.

* min Tools for WordPress 1.0.0.rc1
* Administrator IP whitelist, Never Block these IPs: you can now add dynamic IP domain names instead of IPs by prefixing them with @.
* In multi-site installs, allow the usage as network plugin only
* [HIGH] Site crash when you try activating the plugin on a host with PHP older than 5.4.0. A warning should be issued instead and the plugin should refuse to activate.
* [HIGH] On some servers, site could crash while trying to run Admin Tools cron
* [HIGH] The URL redirection is broken when using a relative existing URL
* [HIGH] Fixed fatal error while building the language strings in auto-prepend mode (however, the attack was already blocked)
* [MEDIUM] RFIShield throws a warning on array arguments
* [MEDIUM] WordPress 4.9 uses a .php file to deliver the editor's JavaScript file. Now it's added to the default exceptions.
* [LOW] Default state of protect from plugin deactivation should be "disabled"
* [LOW] Remove temporary data from the database after uninstall
* [LOW] No icon displayed on multisite installations
* [LOW] Optional fonts were included in the package, bloating its size

* min Tools for WordPress 1.0.0.b3
* PHPShield feature now will block additional stream wrappers
* Added feature to check if user password is inside any dictionary of leaked passwords. This feature is disabled by default.
* Make all Admin Tools strings compatible with WordPress' translation feature
* Remove IP workarounds "recommended setting" notice due to high rate of false detections
* [HIGH] Fixed fetching database connection info from the configuration
* [HIGH] Fatal error trying to use the Quick Setup wizard on IIS
* [LOW] Malware Scanner progress did not appear in a modal
* [LOW] The Optimize WAF must only be shown on servers which support .htaccess files
* [LOW] Missing error strings for the GeoIP database update

* min Tools for WordPress 1.0.0.b2
* Added option to set custom Referer Policy header
* Improved layout for the Configure WAF page
* Improved layout for the Quick Setup page
* Removed "Administrator secret URL parameter" feature, if you want to protect your admin/login, use the "Change admin URL" feature
* [HIGH] Fixed fatal error in Core version about using a Pro only feature
* [HIGH] Internal URLs broken in multisite installations
* [HIGH] The user's IP reported in various places, e.g. Quick Setup Wizard, is wrong when there is a reverse proxy, CDN, etc in front of the site
* [HIGH] It was not possible to run the FileScanner from the CLI
* [MEDIUM] Checkboxes in the GeoBlock page have zero height when unchecked
* [MEDIUM] Fixed parsing WordPress configuration when special chars are used
* [LOW] Fixed output buffering conflicts with some plugins
* [LOW] Changed action names from Publish/Unpublish to Mark safe/Unmark safe in the Scan Alerts page
* [LOW] Automatically allow access to TinyMCE assets file

* min Tools for WordPress 1.0.0.b1
* First public release


== Upgrade Notice ==

Please consult our site.