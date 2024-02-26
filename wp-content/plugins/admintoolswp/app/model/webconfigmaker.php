<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Date\Date;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\AdminTools\Library\Uri\Uri;
use DateTime;
use DateTimeZone;

class WebConfigMaker extends ServerConfigMaker
{
	/**
	 * The current configuration of this feature
	 *
	 * @var  object
	 */
	protected $config = null;

	/**
	 * The default configuration of this feature.
	 *
	 * Note that you define an array. It becomes an object in the constructor. We have to do that since PHP doesn't
	 * allow the initialization of anonymous objects (like e.g. Javascript) but lets us typecast an array to an object
	 * – just not in the property declaration!
	 *
	 * @var  object
	 */
	public $defaultConfig = array(
		// == System configuration ==
		// Host name for HTTPS requests (without https://)
		'httpshost'           => '',
		// Host name for HTTP requests (without http://)
		'httphost'            => '',
		// Base directory of your site (/ for domain's root)
		'rewritebase'         => '',

		// == Optimization and utility ==
		// Force index.php parsing before index.html
		'fileorder'           => 1,
		// Set default expiration time to 1 hour
		'exptime'             => 1,
		// Automatically compress static resources
		'autocompress'        => 1,
		// Redirect index.php to root
		'autoroot'            => 1,
		// Redirect www and non-www addresses
		'wwwredir'            => 0,
		// Redirect old to new domain
		'olddomain'           => '',
		// Force HTTPS for these URLs
		'httpsurls'           => array(),
		// HSTS Header (for HTTPS-only sites)
		'hstsheader'          => 0,
		// Disable HTTP methods TRACE and TRACK (protect against XST)
		'notracetrack'        => 0,
		// Cross-Origin Resource Sharing (CORS)
		'cors'                => 0,
		// Set UTF-8 charset as default
		'utf8charset'         => 0,
		// Send ETag
		'etagtype'            => 'default',
		// Referrer policy
		'referrerpolicy'	  => 'unsafe-url',

		// == Basic security ==
		// Disable directory listings
		'nodirlists'          => 0,
		// Protect against common file injection attacks
		'fileinj'             => 1,
		// Disable PHP Easter Eggs
		'phpeaster'           => 1,
		// Block access from specific user agents
		'nohoggers'           => 0,
		// Block access to configuration.php-dist and htaccess.txt
		'leftovers'           => 1,
		// Protect against clickjacking
		'clickjacking'        => 0,
		// Reduce MIME type security risks
		'reducemimetyperisks' => 0,
		// Reflected XSS prevention
		'reflectedxss'        => 0,
		// Remove Apache and PHP version signature
		'noserversignature'   => 1,
		// Prevent content transformation
		'notransform'         => 0,
		// User agents to block (one per line)
		'hoggeragents'        => array(
			'WebBandit',
			'webbandit',
			'Acunetix',
			'binlar',
			'BlackWidow',
			'Bolt 0',
			'Bot mailto:craftbot@yahoo.com',
			'BOT for JCE',
			'casper',
			'checkprivacy',
			'ChinaClaw',
			'clshttp',
			'cmsworldmap',
			'comodo',
			'Custo',
			'Default Browser 0',
			'diavol',
			'DIIbot',
			'DISCo',
			'dotbot',
			'Download Demon',
			'eCatch',
			'EirGrabber',
			'EmailCollector',
			'EmailSiphon',
			'EmailWolf',
			'Express WebPictures',
			'extract',
			'ExtractorPro',
			'EyeNetIE',
			'feedfinder',
			'FHscan',
			'FlashGet',
			'flicky',
			'GetRight',
			'GetWeb!',
			'Go-Ahead-Got-It',
			'Go!Zilla',
			'grab',
			'GrabNet',
			'Grafula',
			'harvest',
			'HMView',
			'ia_archiver',
			'Image Stripper',
			'Image Sucker',
			'InterGET',
			'Internet Ninja',
			'InternetSeer.com',
			'jakarta',
			'Java',
			'JetCar',
			'JOC Web Spider',
			'kmccrew',
			'larbin',
			'LeechFTP',
			'libwww',
			'Mass Downloader',
			'Maxthon$',
			'microsoft.url',
			'MIDown tool',
			'miner',
			'Mister PiX',
			'NEWT',
			'MSFrontPage',
			'Navroad',
			'NearSite',
			'Net Vampire',
			'NetAnts',
			'NetSpider',
			'NetZIP',
			'nutch',
			'Octopus',
			'Offline Explorer',
			'Offline Navigator',
			'PageGrabber',
			'Papa Foto',
			'pavuk',
			'pcBrowser',
			'PeoplePal',
			'planetwork',
			'psbot',
			'purebot',
			'pycurl',
			'RealDownload',
			'ReGet',
			'Rippers 0',
			'SeaMonkey$',
			'sitecheck.internetseer.com',
			'SiteSnagger',
			'skygrid',
			'SmartDownload',
			'sucker',
			'SuperBot',
			'SuperHTTP',
			'Surfbot',
			'tAkeOut',
			'Teleport Pro',
			'Toata dragostea mea pentru diavola',
			'turnit',
			'vikspider',
			'VoidEYE',
			'Web Image Collector',
			'Web Sucker',
			'WebAuto',
			'WebCopier',
			'WebFetch',
			'WebGo IS',
			'WebLeacher',
			'WebReaper',
			'WebSauger',
			'Website eXtractor',
			'Website Quester',
			'WebStripper',
			'WebWhacker',
			'WebZIP',
			'Wget',
			'Widow',
			'WWW-Mechanize',
			'WWWOFFLE',
			'Xaldon WebSpider',
			'Yandex',
			'Zeus',
			'zmeu',
			'CazoodleBot',
			'discobot',
			'ecxi',
			'GT::WWW',
			'heritrix',
			'HTTP::Lite',
			'HTTrack',
			'ia_archiver',
			'id-search',
			'id-search.org',
			'IDBot',
			'Indy Library',
			'IRLbot',
			'ISC Systems iRc Search 2.1',
			'LinksManager.com_bot',
			'linkwalker',
			'lwp-trivial',
			'MFC_Tear_Sample',
			'Microsoft URL Control',
			'Missigua Locator',
			'panscient.com',
			'PECL::HTTP',
			'PHPCrawl',
			'PleaseCrawl',
			'SBIder',
			'Snoopy',
			'Steeler',
			'URI::Fetch',
			'urllib',
			'Web Sucker',
			'webalta',
			'WebCollage',
			'Wells Search II',
			'WEP Search',
			'zermelo',
			'ZyBorg',
			'Indy Library',
			'libwww-perl',
			'Go!Zilla',
			'TurnitinBot',
			'sqlmap',
		),

		// == Server protection ==
		// -- Toggle protection
		'siteprot'         => 0,
		// -- Fine-tuning
		// File types allowed
		'extypes'          => array(
			'7z', 'appcache', 'atom', 'avi', 'bbaw', 'bmp', 'crx', 'css', 'cur', 'doc', 'docx', 'eot', 'f4a', 'f4b', 'f4p', 'f4v', 'flv', 'geojson', 'gif', 'htc', 'htm', 'html', 'ico', 'jpeg', 'jpe', 'jpg', 'jp2', 'jpe2', 'js', 'json', 'jsonl', 'jsond', 'm4a', 'm4v', 'manifest', 'map', 'mkv', 'mp3', 'mp4', 'mpg', 'mpeg', 'ods', 'odp', 'odt', 'oex', 'oga', 'ogg', 'ogv', 'opus', 'otf', 'png', 'pdf', 'png', 'ppt', 'pptx', 'rar', 'rdf', 'rss', 'safariextz', 'svg', 'svgz', 'swf', 'tar', 'topojson', 'tbz', 'tbz2', 'tgz', 'ttc', 'ttf', 'txt', 'txz', 'vcard', 'vcf', 'vtt', 'wav', 'webapp', 'webm', 'webp', 'woff', 'woff2', 'xloc', 'xls', 'xlsx', 'xml', 'xpi', 'xps', 'xz', 'zip', 'xsl',
			'7Z', 'APPCACHE', 'ATOM', 'AVI', 'BBAW', 'BMP', 'CRX', 'CSS', 'CUR', 'DOC', 'DOCX', 'EOT', 'F4A', 'F4B', 'F4P', 'F4V', 'FLV', 'GEOJSON', 'GIF', 'HTC', 'HTM', 'HTML', 'ICO', 'JPEG', 'JPE', 'JPG', 'JP2', 'JPE2', 'JS', 'JSON', 'JSONL', 'JSOND', 'M4A', 'M4V', 'MANIFEST', 'MAP', 'MKV', 'MP3', 'MP4', 'MPG', 'MPEG', 'ODS', 'ODP', 'ODT', 'OEX', 'OGA', 'OGG', 'OGV', 'OPUS', 'OTF', 'PNG', 'PDF', 'PNG', 'PPT', 'PPTX', 'RAR', 'RDF', 'RSS', 'SAFARIEXTZ', 'SVG', 'SVGZ', 'SWF', 'TAR', 'TOPOJSON', 'TBZ', 'TBZ2', 'TGZ', 'TTC', 'TTF', 'TXT', 'TXZ', 'VCARD', 'VCF', 'VTT', 'WAV', 'WEBAPP', 'WEBM', 'WEBP', 'WOFF', 'WOFF2', 'XLOC', 'XLS', 'XLSX', 'XML', 'XPI', 'XPS', 'XZ', 'ZIP',
		),
		// Where are the file types allowed
		'exdirs' => array(
		),
		// -- Exceptions
		// Allow direct access to these files
		'exceptionfiles'      => array(
			'wp-activate.php',
			'wp-comments-post.php',
			'wp-cron.php',
			'wp-links-opml.php',
			'wp-mail.php',
			'wp-signup.php',
			'wp-trackback.php',
			'xmlrpc.php',
			'wp-content/plugins/akeebabackupwp/app/index.php',
			'wp-content/plugins/akeebabackupwp/app/restore.php',
			'wp-content/plugins/akeebabackupwp/app/remote.php',
		),
		// Allow direct access, except .php files, to these directories
		'exceptiondirs'       => array(
			'.well-known',
		),
		// Allow direct access, including .php files, to these directories
		'fullaccessdirs'      => array(
			'wp-content/upgrade'
		),
	);

	/**
	 * The current configuration of this feature
	 *
	 * @var  object
	 */
	protected $configKey = 'wcconfig';

	/**
	 * The base name of the configuration file being saved by this feature, e.g. ".htaccess". The file is always saved
	 * in the site's root. Any old files under that name are renamed with a .admintools suffix.
	 *
	 * @var string
	 */
	protected $configFileName = 'web.config';

	/**
	 * Nukes current server configuration file, removing all custom rules added by Admin Tools
	 */
	public function nuke()
	{
		// Do nothing
	}

	/**
	 * Compile and return the contents of the web.config configuration file
	 *
	 * @return string
	 */
	public function makeConfigFile()
	{
		$date    = new Date();
		$tz   = new DateTimeZone(Wordpress::get_timezone_string());
		$date->setTimezone($tz);

		$d       = $date->format('Y-m-d H:i:s T', true);
		$version = ADMINTOOLSWP_VERSION;

		$webConfig = <<< XML
<?xml version="1.0" encoding="utf-8"?>
<!--
	Security Enhanced & Highly Optimized .web.config File for WordPress
	automatically generated by Admin Tools $version on $d

	Admin Tools is Free Software, distributed under the terms of the GNU
	General Public License version 3 or, at your option, any later version
	published by the Free Software Foundation.

	!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! IMPORTANT !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	!!                                                                       !!
	!!  If you get an Internal Server Error 500 or a blank page when trying  !!
	!!  to access your site, remove this file and try tweaking its settings  !!
	!!  in the back-end of the Admin Tools component.                        !!
	!!                                                                       !!
	!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
-->
<configuration>
	<system.webServer>

XML;

		$config = $this->loadConfiguration();

		if ($config->fileorder == 1)
		{
			$webConfig .= <<< XML
		<!-- File execution order -->
		<defaultDocument enabled="true">
			<files>
				<clear />
				<add value="index.php" />
				<add value="index.html" />
				<add value="index.htm" />
			</files>
		</defaultDocument>

XML;
		}

		if ($config->nodirlists == 1)
		{
			$webConfig .= <<< XML
		<!-- No directory listings -->
		<directoryBrowse enabled="false" />

XML;
		}

		if ($config->exptime != 0)
		{
			$setEtag  = ($config->etagtype == 'none') ? 'setEtag="false"' : '';
			$eTagInfo = ($config->etagtype == 'none') ? '// Send ETag: false (IIS only supports true/false for ETags)' : '';
			$expTime  = ($config->exptime == 1) ? 604800 : 31708800;

			$webConfig .= <<< XML
		<!-- Optimal default expiration time $eTagInfo -->
		<staticContent>
			<clientCache cacheControlMode="UseMaxAge" cacheControlMaxAge="$expTime" $setEtag />
		</staticContent>

XML;
		}

		if ($config->autocompress == 1)
		{
			$webConfig .= <<<XML
		<urlCompression doStaticCompression="false" doDynamicCompression="true" />
		<httpCompression>
			<dynamicTypes>
				<clear />
				<add mimeType="text/html" enabled="true" />
				<add mimeType="text/plain" enabled="true" />
				<add mimeType="text/xml" enabled="true" />
				<add mimeType="text/css" enabled="true" />
				<add mimeType="message/*" enabled="true" />
				<add mimeType="application/xml" enabled="true" />
				<add mimeType="application/xhtml+xml" enabled="true" />
				<add mimeType="application/rss+xml" enabled="true" />
				<add mimeType="application/javascript" enabled="true" />
				<add mimeType="application/x-javascript" enabled="true" />
				<add mimeType="image/svg+xml" enabled="true" />
				<add mimeType="*/*" enabled="false" />
			</dynamicTypes>
		</httpCompression>

XML;
		}


		$webConfig .= <<< XML
		<rewrite>
			<rules>
				<clear />

XML;

		if (!empty($config->hoggeragents) && ($config->nohoggers == 1))
		{
			$conditions   = '';
			$patternCache = array();

			foreach ($config->hoggeragents as $agent)
			{
				$patternCache[] = $agent;

				if (count($agent) < 10)
				{
					continue;
				}

				$newPattern = implode('|', $patternCache);
				$conditions .= <<< XML
<add input="{HTTP_USER_AGENT}" pattern="$newPattern" />

XML;
				$patternCache = array();
			}

			if (count($patternCache))
			{
				$newPattern = implode('|', $patternCache);
				$conditions .= <<< XML
						<add input="{HTTP_USER_AGENT}" pattern="$newPattern" />

XML;
			}

			$webConfig .= <<< XML
				<rule name="Common hacking tools and bandwidth hoggers block" stopProcessing="true">
					<match url=".*" />
					<conditions logicalGrouping="MatchAny" trackAllCaptures="false">
$conditions
					</conditions>
					<action type="CustomResponse" statusCode="403" statusReason="Forbidden: Access is denied." statusDescription="You do not have permission to view this directory or page using the credentials that you supplied." />
				</rule>

XML;
		}

		if ($config->autoroot)
		{
			$webConfig .= <<<XML
				<rule name="Redirect index.php to /" stopProcessing="true">
					<match url="^index\.php$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll">
						<add input="{THE_REQUEST}" pattern="^POST" ignoreCase="false" negate="true" />
						<add input="{THE_REQUEST}" pattern="^[A-Z]{3,9}\ /index\.php\ HTTP/" ignoreCase="false" />
						<add input="{HTTPS}>s" pattern="^(1>(s)|0>s)$" ignoreCase="false" />
					</conditions>
					<action type="Redirect" url="http{C:2}://{HTTP_HOST}:{SERVER_PORT }/" redirectType="Permanent" />
				</rule>

XML;
		}

		switch ($config->wwwredir)
		{
			case 1:
				// If I have a rewriteBase condition, I have to append it here
				$subfolder = trim($config->rewritebase, '/') ? trim($config->rewritebase, '/').'/' : '';

				// non-www to www
				$webConfig .= <<<END
				<rule name="Redirect non-www to www" stopProcessing="true">
					<match url="^(.*)$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll">
						<add input="{HTTP_HOST}" pattern="^www\." negate="true" />
					</conditions>
					<action type="Redirect" url="http://www.{HTTP_HOST}/$subfolder{R:1}" redirectType="Found" />
				</rule>

END;
				break;

			case 2:
				// www to non-www
				$webConfig .= <<<END
				<rule name="Redirect www to non-www" stopProcessing="true">
					<match url="^(.*)$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll">
						<add input="{HTTP_HOST}" pattern="^www\.(.+)$" />
					</conditions>
					<action type="Redirect" url="http://{C:1}/{R:1}" redirectType="Found" />
				</rule>

END;
				break;
		}

		if (!empty($config->olddomain))
		{
			$domains = trim($config->olddomain);
			$domains = explode(',', $domains);
			$newdomain = $config->httphost;

			foreach ($domains as $olddomain)
			{
				$olddomain = trim($olddomain);
				$originalOldDomain = $olddomain;

				if (empty($olddomain))
				{
					continue;
				}

				$olddomain = $this->escape_string_for_regex($olddomain);

				$webConfig .= <<<END
				<rule name="Redirect old to new domain ($originalOldDomain)" stopProcessing="true">
					<match url="(.*)" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll">
						<add input="{HTTP_HOST}" pattern="^$olddomain" />
					</conditions>
					<action type="Redirect" url="http://$newdomain/{R:1}" redirectType="Found" />
				</rule>

END;
			}
		}

		if (!empty($config->httpsurls))
		{
			$webConfig .= "<!-- Force HTTPS for certain pages -->\n";
			foreach ($config->httpsurls as $url)
			{
				$urlesc = '^' . $this->escape_string_for_regex($url) . '$';
				$webConfig .= <<<END
				<rule name="Force HTTPS for $url" stopProcessing="true">
					<match url="^$urlesc$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAny">
						<add input="{HTTPS}" pattern="0" />
					</conditions>
					<action type="Redirect" url="https://{$config->httpshost}/$url" redirectType="Found" />
				</rule>

END;
			}
		}

		$webConfig .= <<<END
				<rule name="Block out some common exploits">
					<match url=".*" ignoreCase="false" />
					<conditions logicalGrouping="MatchAny" trackAllCaptures="false">
						<add input="{QUERY_STRING}" pattern="proc/self/environ" ignoreCase="false" />
						<add input="{QUERY_STRING}" pattern="base64_(en|de)code\(.*\)" ignoreCase="false" />
						<add input="{QUERY_STRING}" pattern="(&lt;|%3C).*script.*(>|%3E)" />
						<add input="{QUERY_STRING}" pattern="GLOBALS(=|\[|\%[0-9A-Z]{0,2})" ignoreCase="false" />
						<add input="{QUERY_STRING}" pattern="_REQUEST(=|\[|\%[0-9A-Z]{0,2})" ignoreCase="false" />
					</conditions>
					<action type="CustomResponse" url="index.php" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
				</rule>

END;

		if ($config->fileinj == 1)
		{
			$webConfig .= <<<END
				<rule name="File injection protection" stopProcessing="true">
					<match url=".*" ignoreCase="false" />
					<conditions logicalGrouping="MatchAny" trackAllCaptures="false">
						<add input="{QUERY_STRING}" pattern="[a-zA-Z0-9_]=http://" ignoreCase="false" />
						<add input="{QUERY_STRING}" pattern="[a-zA-Z0-9_]=(\.\.//?)+" ignoreCase="false" />
						<add input="{QUERY_STRING}" pattern="[a-zA-Z0-9_]=/([a-z0-9_.]//?)+" />
					</conditions>
					<action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
				</rule>

END;
		}

		$webConfig .= "                <!-- Advanced server protection rules exceptions -->\n";

		if (!empty($config->exceptionfiles))
		{
			$ruleCounter = 0;

			foreach ($config->exceptionfiles as $file)
			{
				$ruleCounter++;
				$file = '^' . $this->escape_string_for_regex($file) . '$';
				$webConfig .= <<<END
				<rule name="Advanced server protection rules exception #$ruleCounter" stopProcessing="true">
					<match url="$file" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
					<action type="None" />
				</rule>

END;
			}
		}

		if (!empty($config->exceptiondirs))
		{
			$ruleCounter = 0;

			foreach ($config->exceptiondirs as $dir)
			{
				$ruleCounter++;
				$dir = trim($dir, '/');
				$dir = $this->escape_string_for_regex($dir);
				$webConfig .= <<<END
				<rule name="Allow access to folders except .php files #$ruleCounter" stopProcessing="true">
					<match url="^$dir/" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false">
						<add input="{REQUEST_FILENAME}" pattern="(\.php)$" ignoreCase="false" negate="true" />
						<add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" />
					</conditions>
					<action type="None" />
				</rule>

END;
			}
		}

		if (!empty($config->fullaccessdirs))
		{
			$ruleCounter = 0;

			foreach ($config->fullaccessdirs as $dir)
			{
				$ruleCounter++;
				$dir = trim($dir, '/');
				$dir = $this->escape_string_for_regex($dir);
				$webConfig .= <<<END
				<rule name="Allow access to folders, including .php files #$ruleCounter" stopProcessing="true">
					<match url="^$dir/" ignoreCase="false" />
					<action type="None" />
				</rule>

END;
			}
		}

		if ($config->phpeaster == 1)
		{
			$webConfig .= <<<END
				<rule name="PHP Easter Egg protection">
					<match url=".*" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false">
						<add input="{QUERY_STRING}" pattern="\=PHP[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}" />
					</conditions>
					<action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
				</rule>

END;
		}

		if ($config->siteprot == 1)
		{
			$exDirs = implode('|', $config->exdirs);
			$exTypes = implode('|', $config->extypes);
			$escapedExdirs = empty($exDirs) ? '' : "^($exDirs)/.*\\";
			$webConfig .= <<<END
				<!-- Allow access to public area files -->
				<rule name="WordPress protection - allow public site access" stopProcessing="true">
					<match url="^index\.php$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
					<action type="None" />
				</rule>
				<rule name="WordPress protection - allow logging in" stopProcessing="true">
					<match url="^wp-login\.php$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
					<action type="None" />
				</rule>
				<!-- Allow access to wp-admin files -->
				<rule name="WordPress protection - access wp-admin directory" stopProcessing="true">
					<match url="^wp-admin/?$" ignoreCase="false" />
					<action type="None" />
				</rule>
				<rule name="WordPress protection - wp-admin main .php files" stopProcessing="true">
					<match url="^wp-admin/[a-zA-Z0-9-]{1,}\.php$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
					<action type="None" />
				</rule>
				<rule name="WordPress protection - wp-admin additional .php files" stopProcessing="true">
					<match url="^wp-admin/(maint|network|user)/[a-zA-Z0-9-]{1,}\.php$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
					<action type="None" />
				</rule>

				<rule name="WordPress protection - Allow access to Admin Tools frontend scanner" stopProcessing="true">
					<match url="^wp-content/plugins/admintoolswp/filescanner\.php$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
					<action type="None" />
				</rule>
				
				<rule name="WordPress protection - allow access to static media files" stopProcessing="true">
					<match url="$escapedExdirs.($exTypes)$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
					<action type="None" />
				</rule>
				
				<rule name="WordPress protection - Block access to all PHP files except index.php">
					<match url="(.*\.php)$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false">
						<add input="{REQUEST_FILENAME}" pattern="(\.php)$" ignoreCase="false" />
						<add input="{REQUEST_FILENAME}" pattern="(/index?\.php)$" ignoreCase="false" negate="true" />
						<add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" />
					</conditions>
					<action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
				</rule>

				<rule name="WordPress protection - Block access to common server configuration files">
					<match url="^(htaccess\.txt|configuration\.php-dist|php\.ini|.user\.ini|web\.config|web\.config\.txt|nginx\.conf)$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
					<action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
				</rule>

END;
		}

		if ($config->leftovers == 1)
		{
			$webConfig .= <<<END
				<rule name="WordPress protection - Forbid access to leftover WordPress files">
					<match url="^(htaccess\.txt|wp-config-sample\.php|license\.txt|readme\.html)$" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
					<action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
				</rule>

END;
		}

		$webConfig .= <<< XML
				<rule name="Improved replacement for WordPress URL rewriting">
					<match url="(.*)" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false">
						<!--<add input="{URL}" pattern="^/index.php" ignoreCase="true" negate="true" />-->
						<add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
						<add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
					</conditions>
					<action type="Rewrite" url="index.php" />
				</rule>

			</rules>

XML;

		if ($config->noserversignature == 1)
		{
			$webConfig .= <<< XML
		<!-- Remove IIS version signature -->
		<outboundRules>
		  <rule name="Remove RESPONSE_Server">
			<match serverVariable="RESPONSE_Server" pattern=".+" />
			<action type="Rewrite" value="MYOB" />
		  </rule>
		</outboundRules>

XML;
		}

		$webConfig .= <<< XML
		</rewrite>
		<httpProtocol>
			<customHeaders>

XML;

		if ($config->clickjacking == 1)
		{
			$webConfig .= <<< ENDCONF
				<!-- Protect against clickjacking / Forbid displaying in FRAME -->
				<add name="X-Frame-Options" value="SAMEORIGIN" />

ENDCONF;
		}

		if ($config->reducemimetyperisks == 1)
		{
			$webConfig .= <<< XML
				<!-- Reduce MIME type security risks -->
				<add name="X-Content-Type-Options" value="nosniff" />

XML;
		}

		if ($config->reflectedxss == 1)
		{
			$webConfig .= <<< XML
				<!-- Reflected XSS prevention -->
				<add name="X-XSS-Protection" value="1; mode=block" />

XML;
		}

		if ($config->noserversignature == 1)
		{
			$webConfig .= <<< XML
				<!-- Remove IIS and PHP version signature -->
				<remove name="X-Powered-By" />
				<add name="X-Powered-By" value="MYOB" />
				<remove name="X-Content-Powered-By" />
				<add name="X-Content-Powered-By" value="MYOB" />

XML;

		}

		if ($config->notransform == 1)
		{
			$webConfig .= <<< XML
				<!-- Prevent content transformation -->
				<add name="Cache-Control" value="no-transform" />

XML;
		}

		if ($config->hstsheader == 1)
		{
			$webConfig .= <<<XML
				<!-- HSTS Header - See http://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security -->
				<add name="Strict-Transport-Security" value="max-age=31536000" />

XML;
		}

		if ($config->cors == 1)
		{
			$webConfig .= <<<XML
				<!-- Cross-Origin Resource Sharing (CORS) - See http://enable-cors.org/ -->
				<add name="Access-Control-Allow-Origin" value="*" />
				<add name="Timing-Allow-Origin" value="*" />

XML;
		}
		elseif ($config->cors == -1)
		{
			$webConfig .= <<<XML
				<!-- Explicitly disable Cross-Origin Resource Sharing (CORS) - See http://enable-cors.org/ -->
				<add name="Cross-Origin-Resource-Policy" value="same-origin" />

XML;
		}

		if ($config->referrerpolicy !== '-1')
		{
			$webConfig .= <<<XML
				<!-- Referrer-policy -->
				<add name="Referrer-Policy" value="{$config->referrerpolicy}" />

XML;
		}

		$webConfig .= <<< XML
			</customHeaders>
		</httpProtocol>

XML;

		if ($config->notracetrack == 1)
		{
			$webConfig .= <<<XML
		<!-- Disable HTTP methods TRACE and TRACK (protect against XST) -->
		<security>
			<requestFiltering>
				<verbs>
					<add verb="TRACE" allowed="false" />
					<add verb="TRACK" allowed="false" />
				</verbs>
			</requestFiltering>
		</security>

XML;
		}

		$webConfig .= <<< XML
	</system.webServer>
</configuration>

XML;

		return $webConfig;
	}
}
