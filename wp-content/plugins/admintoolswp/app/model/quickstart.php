<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\AdminTools\Library\Registry\Registry;
use Akeeba\AdminTools\Library\Uri\Uri;
use Akeeba\AdminTools\Library\Utils\Ip;

class QuickStart extends Model
{
	/** @var   Storage          The parameters storage model */
	private $storageModel;

	/** @var   AdminPassword    Administrator password protection model */
	private $adminPasswordModel;

	/** @var   ConfigureWAF     WAF Config model */
	private $wafModel;

	/** @var   array            WAF configuration */
	private $config;

	public function  __construct($input)
	{
		parent::__construct($input);

		$this->storageModel       = Storage::getInstance();
		$this->adminPasswordModel = new AdminPassword($this->input);
		$this->wafModel           = new ConfigureWAF($this->input);
		$this->config             = $this->wafModel->getItems();
	}

	/**
	 * Applies the wizard preferences to the component's configuration
	 *
	 * @param   array   $preferences    Preferences that should be applied
	 *
	 * @return  void
	 */
	public function applyPreferences(array $preferences)
	{
		// Let's use a registry object so we can supply default values
		$preferences = new Registry($preferences);

		// Reset all stored settings
		$this->storageModel->resetContents();

		// Password protect administrator
		$this->applyAdministratorPassword($preferences);

		// Apply email on admin login
		$this->config['emailonadminlogin'] = $preferences->get('emailonadminlogin', '');
		$this->config['emailonfailedadminlogin'] = $preferences->get('emailonadminlogin', '');

		// Apply IP whitelist
		$this->applyIpWhitelist($preferences);

		// Disable editing backend users' properties
		$this->config['nonewadmins'] = $preferences->get('nonewadmins', 0);

		// Enable WAF
		$this->applyWafPreferences($preferences->get('enablewaf', 0));

		// Apply IP workarounds
		$this->config['ipworkarounds'] = $preferences->get('ipworkarounds', 0);

		// Apply IP autoban preferences
		$this->applyAutoban($preferences->get('autoban', 0));

		// Apply automatic permanent blacklist
		$this->applyBlacklist($preferences->get('autoblacklist', 0));

		// Apply email address to report WAF exceptions and blocks
		$this->config['emailbreaches'] = $preferences->get('emailbreaches', '');
		$this->config['emailafteripautoban'] = $preferences->get('emailbreaches', '');

		// Project Honeypot HTTP:BL
		$this->applyProjectHoneypot($preferences->get('bbhttpblkey', ''));

		// Save the WAF configuration
		$this->wafModel->saveConfig($this->config);

		// Apply .htaccess Maker
		if ($this->input->getInt('htmaker', 0))
		{
			$written = $this->applyHtmaker();
		}

		// Save a flag indicating we no longer need to run the Quick Start
		$this->storageModel->load();
		$this->storageModel->setValue('quickstart', 1, 1);
	}

	/**
	 * Password protect / unprotect administrator
	 *
	 * @param   Registry    $preferences
	 *
	 * @return  void
	 */
	private function applyAdministratorPassword($preferences)
	{
		$this->adminPasswordModel->username = $preferences->get('admin_username', '');
		$this->adminPasswordModel->password = $preferences->get('admin_password', '');

		if (empty($this->adminPasswordModel->username) || empty($this->adminPasswordModel->password))
		{
			$this->adminPasswordModel->unprotect();
		}
		else
		{
			$this->adminPasswordModel->protect();
		}
	}

	/**
	 * Apply administrator IP whitelist
	 *
	 * @param   Registry    $preferences
	 *
	 * @return  void
	 */
	private function applyIpWhitelist($preferences)
	{
		if (!defined('ADMINTOOLSWP_PRO') || !ADMINTOOLSWP_PRO ||
			!class_exists('Akeeba\AdminTools\Admin\Model\WhitelistedAddresses')
		)
		{
			return;
		}

		$this->config['ipwl'] = $preferences->get('ipwl', 0);

		if ($this->config['ipwl'])
		{
			/** @var WhitelistedAddresses $ipwlModel */
			$ipwlModel = new WhitelistedAddresses($this->input);
			$tableName = $ipwlModel->getTableName();

			$db = $this->getDbo();
			$db->truncateTable($tableName);

			$detectedIp = $preferences->get('detectedip', '');

			if (!empty($detectedIp) && ($detectedIp != Ip::getIp()))
			{
				$ipwlModel->save(array(
					'ip'          => $preferences->get('detectedip', ''),
					'description' => Language::_('COM_ADMINTOOLS_QUICKSTART_MSG_IPADDEDBYWIZARD')
				));
			}
			else
			{
				$ipwlModel->save(array(
					'ip'          => Ip::getIp(),
					'description' => Language::_('COM_ADMINTOOLS_QUICKSTART_MSG_IPADDEDBYWIZARD')
				));
			}
		}
	}

	/**
	 * Apply main WAF preference (global disable/enable)
	 *
	 * @param   bool  $enabled  Should I enable WAF?
	 *
	 * @return  void
	 */
	private function applyWafPreferences($enabled = true)
	{
		$state = $enabled ? 1 : 0;

		$newValues = array(
			'ipbl'                    => $state,
			'sqlishield'              => $state,
			'antispam'                => 0,
			'custgenerator'           => $state,
			'generator'               => 'MYOB',
			'logbreaches'             => 1,
			'csrfshield'              => 0,
			'rfishield'               => $state,
			'uploadshield'            => $state,
			'trackfailedlogins'       => $state,
			'use403view'              => 0,
			'iplookup'                => 'ip-lookup.net/index.php?ip={ip}',
			'iplookupscheme'          => 'https',
			'saveusersignupip'        => $state,
			'whitelist_domains'       => '.googlebot.com,.search.msn.com',
			'reasons_nolog'           => 'geoblocking',
			'reasons_noemail'         => 'geoblocking',
			'email_throttle'          => 1,
		    'criticalfiles'           => $state,
		);

		$this->config = array_merge($this->config, $newValues);
	}

	/**
	 * Apply automatic IP ban
	 *
	 * @param   bool  $enabled  Should I enable it?
	 *
	 * @return  void
	 */
	private function applyAutoban($enabled = true)
	{
		$state = $enabled ? 1 : 0;

		$newValues = array(
			'tsrenable'               => $state,
			'tsrstrikes'              => 3,
			'tsrnumfreq'              => 1,
			'tsrfrequency'            => 'minute',
			'tsrbannum'               => 15,
			'tsrbanfrequency'         => 'minute',
		);

		$this->config = array_merge($this->config, $newValues);
	}

	/**
	 * Apply automatic IP ban
	 *
	 * @param   bool  $enabled  Should I enable it?
	 *
	 * @return  void
	 */
	private function applyBlacklist($enabled = true)
	{
		$state = $enabled ? 1 : 0;

		$newValues = array(
			'permaban'                => $state,
			'permabannum'             => 3,
		);

		$this->config = array_merge($this->config, $newValues);
	}

	/**
	 * Apply Project Honeypot HTTP:BL settings
	 *
	 * @param   string  $key  The HTTP:BL key
	 *
	 * @return  void
	 */
	private function applyProjectHoneypot($key = '')
	{
		$state = empty($key) ? 0 : 1;

		$newValues = array(
			'bbhttpblkey'             => $key,
			'httpblenable'            => $state,
			'httpblthreshold'         => 25,
			'httpblmaxage'            => 30,
			'httpblblocksuspicious'   => 0,
		);

		$this->config = array_merge($this->config, $newValues);
	}

	private function applyHtmaker()
	{
		if (!defined('ADMINTOOLSWP_PRO') || !ADMINTOOLSWP_PRO ||
			!class_exists('Akeeba\AdminTools\Admin\Model\HtaccessMaker')
		)
		{
			return true;
		}

		$htMakerModel = new HtaccessMaker($this->input);

		// Get the base bath to the site's root
		$basePath = Uri::base(true);

		if (substr($basePath, -9) == '/wp-admin')
		{
			$basePath = substr($basePath, 9);
		}

		$basePath = trim($basePath, '/');

		$basePath = empty($basePath) ? '/' : '';

		// Get the site's hostname
		$hostname = Uri::getInstance()->getHost();

		// Should I redirect non-www to www or vice versa?
		$wwwRedir = substr($hostname, 0, 4) == 'www.' ? 1 : 2;

		// Is it an HTTPS site?
		$isHttps = Uri::getInstance()->getScheme() == 'https';

		// Get the new .htaccess Maker configuration values
		$newConfig = array(
			// == System configuration ==
			'httpshost'           => $hostname,
			'httphost'            => $hostname,
			'symlinks'            => -1,
			'rewritebase'         => $basePath,

			// == Optimization and utility ==
			'fileorder'           => 1,
			'exptime'             => 2,
			'autocompress'        => 1,
			'forcegzip'           => 1,
			'autoroot'            => 0,
			'wwwredir'            => $wwwRedir,
			'hstsheader'          => $isHttps ? 1 : 0,
			'notracetrack'        => 0,
			'cors'                => 0,
			'utf8charset'         => 0,
			'etagtype'            => 'default',

			// == Basic security ==
			'nodirlists'          => 0,
			'fileinj'             => 1,
			'phpeaster'           => 1,
			'nohoggers'           => 0,
			'leftovers'           => 1,
			'clickjacking'        => 0,
			'reducemimetyperisks' => 0,
			'reflectedxss'        => 0,
			'noserversignature'   => 1,
			'notransform'         => 0,
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
			),

			// == Server protection ==
			'siteprot'            => 0,
			// -- Fine-tuning
			'extypes'             => array(
				'7z', 'appcache', 'atom', 'avi', 'bbaw', 'bmp', 'crx', 'css', 'cur', 'doc', 'docx', 'eot', 'f4a', 'f4b', 'f4p', 'f4v', 'flv', 'geojson', 'gif', 'htc', 'htm', 'html', 'ico', 'jpeg', 'jpe', 'jpg', 'jp2', 'jpe2', 'js', 'json', 'jsonl', 'jsond', 'm4a', 'm4v', 'manifest', 'map', 'mkv', 'mp3', 'mp4', 'mpg', 'mpeg', 'ods', 'odp', 'odt', 'oex', 'oga', 'ogg', 'ogv', 'opus', 'otf', 'png', 'pdf', 'png', 'ppt', 'pptx', 'rar', 'rdf', 'rss', 'safariextz', 'svg', 'svgz', 'swf', 'tar', 'topojson', 'tbz', 'tbz2', 'tgz', 'ttc', 'ttf', 'txt', 'txz', 'vcard', 'vcf', 'vtt', 'wav', 'webapp', 'webm', 'webp', 'woff', 'woff2', 'xloc', 'xls', 'xlsx', 'xml', 'xpi', 'xps', 'xz', 'zip', 'xsl',
				'7Z', 'APPCACHE', 'ATOM', 'AVI', 'BBAW', 'BMP', 'CRX', 'CSS', 'CUR', 'DOC', 'DOCX', 'EOT', 'F4A', 'F4B', 'F4P', 'F4V', 'FLV', 'GEOJSON', 'GIF', 'HTC', 'HTM', 'HTML', 'ICO', 'JPEG', 'JPE', 'JPG', 'JP2', 'JPE2', 'JS', 'JSON', 'JSONL', 'JSOND', 'M4A', 'M4V', 'MANIFEST', 'MAP', 'MKV', 'MP3', 'MP4', 'MPG', 'MPEG', 'ODS', 'ODP', 'ODT', 'OEX', 'OGA', 'OGG', 'OGV', 'OPUS', 'OTF', 'PNG', 'PDF', 'PNG', 'PPT', 'PPTX', 'RAR', 'RDF', 'RSS', 'SAFARIEXTZ', 'SVG', 'SVGZ', 'SWF', 'TAR', 'TOPOJSON', 'TBZ', 'TBZ2', 'TGZ', 'TTC', 'TTF', 'TXT', 'TXZ', 'VCARD', 'VCF', 'VTT', 'WAV', 'WEBAPP', 'WEBM', 'WEBP', 'WOFF', 'WOFF2', 'XLOC', 'XLS', 'XLSX', 'XML', 'XPI', 'XPS', 'XZ', 'ZIP',
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
				'wp-content/upgrade',
			),
		);

		$htMakerModel->saveConfiguration($newConfig);

		return $htMakerModel->writeConfigFile();
	}

	/**
	 * Is it the Quick Setup Wizard's first run?
	 *
	 * @return  bool
	 */
	public function isFirstRun()
	{
		return $this->storageModel->getValue('quickstart', 0) == 0;
	}
}
