<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Uri\Uri;
use Akeeba\AdminTools\Library\Utils\Ip;
use Akeeba\AdminTools\Library\Utils\TimezoneWrangler;

class AtsystemUtilExceptionshandler
{
	/** @var    Params  Plugin parameters */
	protected $params = null;

	/** @var   Storage  Component parameters */
	protected $cparams = null;

	public function __construct(Params &$params, Storage &$cparams)
	{
		$this->params  = $params;
		$this->cparams = $cparams;
	}

	/**
	 * Logs security exceptions and processes the IP auto-ban for this IP
	 *
	 * @param string $reason                   Block reason code
	 * @param string $extraLogInformation      Extra information to be written to the text log file
	 * @param string $extraLogTableInformation Extra information to be written to the extradata field of the log table (useful for JSON format)
	 *
	 * @return bool
	 */
	public function logAndAutoban($reason, $extraLogInformation = '', $extraLogTableInformation = '')
	{
		$ret = $this->logBreaches($reason, $extraLogInformation, $extraLogTableInformation);

		$autoban = $this->cparams->getValue('tsrenable', 0);

		if ($autoban)
		{
			$this->autoBan($reason);
		}

		return $ret;
	}

	/**
	 * Blocks the request in progress and, optionally, logs the details of the
	 * blocked request for the admin to review later
	 *
	 * @param string $reason                   Block reason code
	 * @param string $message                  The message to be shown to the user
	 * @param string $extraLogInformation      Extra information to be written to the text log file
	 * @param string $extraLogTableInformation Extra information to be written to the extradata field of the log table (useful for JSON format)
	 *
	 * @throws Exception
	 */
	public function blockRequest($reason = 'other', $message = '', $extraLogInformation = '', $extraLogTableInformation = '')
	{
		if (empty($message))
		{
			$customMessage = $this->cparams->getValue('custom403msg', '');

			if (!empty($customMessage))
			{
				$message = $customMessage;
			}
			else
			{
				$message = 'ADMINTOOLS_BLOCKED_MESSAGE';
			}
		}

		$r = $this->logBreaches($reason, $extraLogInformation, $extraLogTableInformation);

		if (!$r)
		{
			return;
		}

		$autoban = $this->cparams->getValue('tsrenable', 0);

		if ($autoban)
		{
			$this->autoBan($reason);
		}

		if ((Language::_('ADMINTOOLS_BLOCKED_MESSAGE') == 'ADMINTOOLS_BLOCKED_MESSAGE') && ($message == 'ADMINTOOLS_BLOCKED_MESSAGE'))
		{
			$message = "Access Denied";
		}

		// Show the 403 message
		// If the user is doing an AJAX request, try to send a formatted error message, otherwise WordPress will display
		// a generic "HTTP error" (in WP all AJAX requests will be answered with a 200 status code, no matter what)
		if ($this->isDoingAjax())
		{
			echo json_encode( array(
				'success' => false,
				'data'    => array(
					'code'	   => 403,
					'message'  => $message,
				)
			) );
		}
		else
		{
			// TODO Render a proper template error
			@ob_end_clean();
			header("HTTP/1.0 403 Forbidden");

			echo $message;
		}

		die();
	}

	public function logBreaches($reason, $extraLogInformation = '', $extraLogTableInformation = '')
	{
		$ip = $this->getVisitorIPAddress();

		// No point continuing if I cannot get the visitor's IP address
		if ($ip === false)
		{
			return false;
		}

		// Make sure this IP is not in the "Do not block these IPs" list
		if ($this->isSafeIP($ip))
		{
			return false;
		}

		// Make sure this IP is not in the administrator white list
		if ($this->isIPInAdminWhitelist($ip))
		{
			return false;
		}

		// Make sure this IP doesn't resolve to a whitelisted domain
		if ($this->isWhitelistedDomain($ip))
		{
			return true;
		}

		// Is this a private network IP and IP workaround is off? If so let's raise the flag so we can notify the user
		$this->flagPrivateNetworkIPs();

		// Get the human readable blocking reason
		$txtReason = $this->getBlockingReasonHumanReadable($reason, $extraLogTableInformation);

		// Get the email tokens, also used for logging
		$tokens = $this->getEmailVariables($txtReason);

		// Log the security exception to file and the database, if necessary
		$this->logSecurityException($reason, $extraLogInformation, $extraLogTableInformation, $txtReason, $tokens);

		// Email the security exception, if necessary
		$this->emailSecurityException($reason, $tokens);

		return true;
	}

	/**
	 * Get the visitor IP address. Return false if we cannot get an IP address or if we get 0.0.0.0 (broken IP forwarding).
	 *
	 * @return  bool|string
	 */
	private function getVisitorIPAddress()
	{
		// Get our IP address
		$ip = Ip::getIp();

		if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
		{
			$ip = substr($ip, strrpos($ip, ':') + 1);
		}

		// No point continuing if we can't get an address, right?
		if (empty($ip) || ($ip == '0.0.0.0'))
		{
			return false;
		}

		return $ip;
	}

	/**
	 * Is the IP address in the "Never block these IPs" (safe IPs) list?
	 *
	 * @param	string	$ip		IP Address
	 *
	 * @return bool
	 */
	private function isSafeIP($ip)
	{
		$safeIPs = $this->cparams->getValue('neverblockips', '');

		if (!empty($safeIPs))
		{
			$safeIPs = explode(',', $safeIPs);

			if (!empty($safeIPs))
			{
				if (Ip::IPinList($ip, $safeIPs))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Is the IP address in the Administrator IP Whitelist?
	 *
	 * @param	string	$ip		IP Address
	 *
	 * @return  bool
	 */
	private function isIPInAdminWhitelist($ip)
	{
		if ($this->cparams->getValue('ipwl', 0) == 1)
		{
			$db  = Wordpress::getDb();
			$sql = $db->getQuery(true)
						->select($db->qn('ip'))
						->from($db->qn('#__admintools_adminiplist'));

			$db->setQuery($sql);

			try
			{
				$ipTable = $db->loadColumn();
			}
			catch (Exception $e)
			{
				$ipTable = null;
			}

			if (!empty($ipTable))
			{
				if (Ip::IPinList($ip, $ipTable))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Does the IP address resolve to one of the whitelisted domain names?
	 *
	 * @param   string  $ip
	 *
	 * @return  bool
	 */
	private function isWhitelistedDomain($ip)
	{
		static $whitelist_domains = null;

		if (is_null($whitelist_domains))
		{
			$whitelist_domains = $this->cparams->getValue('whitelist_domains', '.googlebot.com,.search.msn.com');
			$whitelist_domains = explode(',', $whitelist_domains);
		}

		if (!empty($whitelist_domains))
		{
			$remote_domain = @gethostbyaddr($ip);

			if (!empty($remote_domain))
			{
				foreach ($whitelist_domains as $domain)
				{
					$domain = trim($domain);

					if (strrpos($remote_domain, $domain) !== false)
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Get the blocking reason in a human readable format
	 *
	 * @param   string  $reason
	 * @param   string  $extraLogTableInformation
	 *
	 * @return  string
	 */
	private function getBlockingReasonHumanReadable($reason, $extraLogTableInformation)
	{
		// Get the reason in human readable format
		$txtReason = Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_' . strtoupper($reason));

		if (empty($extraLogTableInformation))
		{
			return $txtReason;
		}

		// Get extra information
		list($logReason,) = explode('|', $extraLogTableInformation);

		return $txtReason . " ($logReason)";
	}

	/**
	 * Get the variables we can use in emails as an associative list (variable => value).
	 *
	 * @param   string  $reason           The value for the [REASON] variable
	 * @param   array   $customVariables  An array of custom variables to add to the return.
	 *
	 * @return  array
	 */
	public function getEmailVariables($reason, $customVariables = array())
	{
		// Get our IP address
		$ip = Ip::getIp();

		if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
		{
			$ip = substr($ip, strrpos($ip, ':') + 1);
		}

		// Get the site name
		$sitename = Wordpress::get_option('blogname');

		// Create a link to lookup the IP
		$ipLookupURL = $this->cparams->getValue('iplookupscheme', 'http') . '://' . $this->cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');
		$ipLookupURL = str_replace('{ip}', $ip, $ipLookupURL);

		$uri = Uri::getInstance();
		$url = $uri->toString(['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment']);

		$country   = '';
		$continent = '';

		if (empty($country))
		{
			$country = '(unknown country)';
		}

		if (empty($continent))
		{
			$continent = '(unknown continent)';
		}

		$tzWrangler = new TimezoneWrangler();
		$email_timezone = $this->cparams->getValue('email_timezone', 'AKEEBA/DEFAULT');

		if (!empty($email_timezone) && ($email_timezone != 'AKEEBA/DEFAULT'))
		{
			try
			{
				$tzWrangler->setForcedTimezone($email_timezone);
			}
			catch (Exception $e)
			{
				// Just in case someone puts an invalid timezone in there (you can never be too paranoid).
			}
		}

		$ret = array(
			'[SITENAME]'  => $sitename,
			'[REASON]'    => $reason,
			'[DATE]'      => $tzWrangler->getLocalTimeStamp('Y-m-d H:i:s T'),
			'[URL]'       => $url,
			'[IP]'        => $ip,
			'[LOOKUP]'    => '<a href="' . $ipLookupURL . '">IP Lookup</a>',
			'[COUNTRY]'   => $country,
			'[CONTINENT]' => $continent,
			'[UA]'        => $_SERVER['HTTP_USER_AGENT'],
		);

		if (is_array($customVariables) && !empty($customVariables))
		{
			$ret = array_merge($ret, $customVariables);
		}

		return $ret;
	}

	/**
	 * Write a security exception to the log, as long as logging is enabled and the $reason is not one of the $reasons_nolog ones
	 *
	 * @param   string  $reason
	 * @param   string  $extraLogInformation
	 * @param   string  $extraLogTableInformation
	 * @param   string  $txtReason
	 * @param   array   $tokens
	 *
	 * @return  void
	 */
	private function logSecurityException($reason, $extraLogInformation, $extraLogTableInformation, $txtReason, $tokens)
	{
		$reasons_nolog = $this->cparams->getValue('reasons_nolog', 'geoblocking');
		$reasons_nolog = explode(',', $reasons_nolog);

		if (!$this->cparams->getValue('logbreaches', 0) || in_array($reason, $reasons_nolog))
		{
			return;
		}

		// Log to file
		$this->logSecurityExceptionToFile($reason, $extraLogInformation, $txtReason, $tokens);

		// Log to the database table
		$this->logSecurityExceptionToDatabase($reason, $extraLogTableInformation, $tokens);
	}

	/**
	 * Log a security exception to our log file
	 *
	 * @param   string  $reason
	 * @param   string  $extraLogInformation
	 * @param   string  $txtReason
	 * @param   array   $tokens
	 */
	private function logSecurityExceptionToFile($reason, $extraLogInformation, $txtReason, $tokens)
	{
		// Write to the log file only if we're told to
		if (!$this->cparams->getValue('logfile', 0))
		{
			return;
		}

		// Get the log filename
		$logpath = ADMINTOOLSWP_PATH.'/app/log';
		$fname   = $logpath . DIRECTORY_SEPARATOR . 'admintools_breaches.php';

		// -- Check the file size. If it's over 1Mb, archive and start a new log.
		if (@file_exists($fname))
		{
			$fsize = filesize($fname);

			if ($fsize > 1048756)
			{
				$altFile = substr($fname, 0, -4) . '.1.php';

				if (@file_exists($altFile))
				{
					unlink($altFile);
				}

				@copy($fname, $altFile);
				@unlink($fname);
			}
		}

		// If the main log file does not exist yet create a new one.
		if (!file_exists($fname))
		{
			$content = <<< END
php
/**
 * =====================================================================================================================
 * Admin Tools debug log file
 * =====================================================================================================================
 *
 * This file contains a dump of the requests which were blocked by Admin Tools. By definition, this file does contain
 * a lot of "hacking signatures" since this is what the Admin Tools component is designed to stop and this is the file
 * logging all these hacking attempts.
 *
 * You can disable the creation of this file by going to Admin Tools, Web Application Firewall, Configure
 * WAF and setting the "Keep a debug log file" option to NO. This is the recommended setting. You should only set this
 * option to YES if you are troubleshooting an issue (Admin Tools is blocking access to your site).
 *
 * Some hosts will mistakenly report this file as suspicious or hacked. As a result they might issue an automated
 * warning and / or block access to your site. Should that happen please ask your host to look in this file and read
 * this header. This file is SAFE since the only executable statement is die() below which prevents the file from being
 * executed at all. If your host does not understand that this file is safe or does not know how to add an exception in
 * their automated scanner to exempt Joomla's log files (all files under this directory) from being flagged as hacked /
 * suspicious we strongly recommend going to a different host that understands how PHP works. It will be safer for you
 * as well. 
 */
 
die();
END;
			$content = "?$content?";
			$content .= ">\n\n";
			file_put_contents($fname, '<' . $content);
		}

		// -- Log the exception
		$fp = @fopen($fname, 'a');

		if ($fp === false)
		{
			return;
		}

		fwrite($fp, str_repeat('-', 79) . PHP_EOL);
		fwrite($fp, "Blocking reason: " . $reason . PHP_EOL . str_repeat('-', 79) . PHP_EOL);
		fwrite($fp, "Reason     : " . $txtReason . PHP_EOL);
		fwrite($fp, 'Timestamp  : ' . gmdate('Y-m-d H:i:s') . " GMT" . PHP_EOL);
		fwrite($fp, 'Local time : ' . $tokens['[DATE]'] . " " . PHP_EOL);
		fwrite($fp, 'URL        : ' . $tokens['[URL]'] . PHP_EOL);
		fwrite($fp, 'IP         : ' . $tokens['[IP]'] . PHP_EOL);
		fwrite($fp, 'UA         : ' . $tokens['[UA]'] . PHP_EOL);

		if (!empty($extraLogInformation))
		{
			fwrite($fp, $extraLogInformation . PHP_EOL);
		}

		fwrite($fp, PHP_EOL . PHP_EOL);
		fclose($fp);

	}

	/**
	 * Log a security exception to the database table
	 *
	 * @param   string  $reason
	 * @param   string  $extraLogTableInformation
	 * @param   array   $tokens
	 *
	 *
	 * @since version
	 */
	private function logSecurityExceptionToDatabase($reason, $extraLogTableInformation, $tokens)
	{
		try
		{
			$date     = new DateTime('now', new DateTimeZone('GMT'));
			$db       = Wordpress::getDb();

			$logEntry = (object) array(
				'logdate'   => $date->format('Y-m-d H:i:s'),
				'ip'        => $tokens['[IP]'],
				'url'       => $tokens['[URL]'],
				'reason'    => $reason,
				'extradata' => $extraLogTableInformation,
			);

			$db->insertObject('#__admintools_log', $logEntry);
		}
		catch (Exception $e)
		{
			// Do nothing if the query fails
		}
	}

	/**
	 * Sends information about the security exception by email
	 *
	 * @param   string  $reason
	 * @param   array   $tokens
	 *
	 * @return  bool
	 */
	private function emailSecurityException($reason, $tokens)
	{
		$emailbreaches   = $this->cparams->getValue('emailbreaches', '');
		$reasons_noemail = $this->cparams->getValue('reasons_noemail', 'geoblocking');
		$reasons_noemail = explode(',', $reasons_noemail);

		if (empty($emailbreaches) || in_array($reason, $reasons_noemail))
		{
			return true;
		}

		// Send the email
		// Let's get the most suitable email template
		$template = $this->getEmailTemplate($reason);

		// Got no template, the user didn't published any email template, or the template doesn't want us to
		// send a notification email. Anyway, let's stop here
		if (!$template)
		{
			return true;
		}
		else
		{
			$subject = $template[0];
			$body    = $template[1];
		}

		$subject = str_replace(array_keys($tokens), array_values($tokens), $subject);
		$body    = str_replace(array_keys($tokens), array_values($tokens), $body);

		$recipients = explode(',', $emailbreaches);
		$recipients = array_map('trim', $recipients);

		Wordpress::sendEmail($recipients, $subject, $body);

		return true;
	}

	/**
	 * Are we doing an AJAX request?
	 * Inspects the incoming request and if the path matches it means the user is performing an AJAX request.
	 *
	 * @return bool
	 */
	private function isDoingAjax()
	{
		$ajax_endpoints = array(
			'async-upload.php'		// Media Upload
		);

		$url = Uri::getInstance();

		$request_file = basename($url->getPath());

		return in_array($request_file, $ajax_endpoints);
	}

	/**
	 * Checks if an IP address should be automatically banned for raising too many security exceptions over a predefined
	 * time period.
	 *
	 * @param   string $reason The reason of the ban
	 *
	 * @return  void
	 */
	public function autoBan($reason = 'other')
	{
		// We need to be able to get our own IP, right?
		if (!function_exists('inet_pton'))
		{
			return;
		}

		// Get the IP
		$ip = Ip::getIp();

		// No point continuing if we can't get an address, right?
		if (empty($ip) || ($ip == '0.0.0.0'))
		{
			return;
		}

		// Check for repeat offenses
		$db = Wordpress::getDb();
		$strikes = $this->cparams->getValue('tsrstrikes', 3);
		$numfreq = $this->cparams->getValue('tsrnumfreq', 1);
		$frequency = $this->cparams->getValue('tsrfrequency', 'hour');
		$mindatestamp = 0;

		switch ($frequency)
		{
			case 'second':
				break;

			case 'minute':
				$numfreq *= 60;
				break;

			case 'hour':
				$numfreq *= 3600;
				break;

			case 'day':
				$numfreq *= 86400;
				break;

			case 'ever':
				$mindatestamp = 946706400; // January 1st, 2000
				break;
		}

		$now = new DateTime('now', new DateTimeZone('GMT'));

		if ($mindatestamp == 0)
		{
			$mindatestamp = $now->getTimestamp() - $numfreq;
		}

		$minDate = new DateTime('now', new DateTimeZone('GMT'));
		$minDate->setTimestamp($mindatestamp);
		$minDate = $minDate->format('Y-m-d H:i:s');

		$sql = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__admintools_log'))
			->where($db->qn('logdate') . ' >= ' . $db->q($minDate))
			->where($db->qn('ip') . ' = ' . $db->q($ip));
		$db->setQuery($sql);
		try
		{
			$numOffenses = $db->loadResult();
		}
		catch (Exception $e)
		{
			$numOffenses = 0;
		}

		if ($numOffenses < $strikes)
		{
			return;
		}

		// Block the IP
		$myIP = @inet_pton($ip);

		if ($myIP === false)
		{
			return;
		}

		$myIP = inet_ntop($myIP);

		$until     = $now->getTimestamp();
		$numfreq   = $this->cparams->getValue('tsrbannum', 1);
		$frequency = $this->cparams->getValue('tsrbanfrequency', 'hour');

		switch ($frequency)
		{
			case 'second':
				$until += $numfreq;
				break;

			case 'minute':
				$numfreq *= 60;
				$until += $numfreq;
				break;

			case 'hour':
				$numfreq *= 3600;
				$until += $numfreq;
				break;

			case 'day':
				$numfreq *= 86400;
				$until += $numfreq;
				break;

			case 'ever':
				$until = 2145938400; // January 1st, 2038 (mind you, UNIX epoch runs out on January 19, 2038!)
				break;
		}

		$minDate = new DateTime('now', new DateTimeZone('GMT'));
		$minDate->setTimestamp($until);
		$minDate = $minDate->format('Y-m-d H:i:s');

		$record = (object)array(
			'ip'     => $myIP,
			'reason' => $reason,
			'until'  => $minDate
		);

		// If I'm here it means that we have to ban the user. Let's see if this is a simple autoban or
		// we have to issue a permaban as a result of several attacks
		if ($this->cparams->getValue('permaban', 0))
		{
			// Ok I have to check the number of autoban
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__admintools_ipautobanhistory'))
				->where($db->qn('ip') . ' = ' . $db->q($myIP));

			try
			{
				$bans = $db->setQuery($query)->loadResult();
			}
			catch (Exception $e)
			{
				$bans = 0;
			}

			$limit = (int)$this->cparams->getValue('permabannum', 0);

			if ($limit && ($bans >= $limit))
			{
				$block = (object)array(
					'ip'          => $myIP,
					'description' => 'IP automatically blocked after being banned automatically ' . $bans . ' times'
				);

				try
				{
					$db->insertObject('#__admintools_ipblock', $block);
				}
				catch (Exception $e)
				{
					// This should never happen, however let's prevent a white page if anything goes wrong
				}
			}
		}

		try
		{
			$db->insertObject('#__admintools_ipautoban', $record);
		}
		catch (Exception $e)
		{
			// If the IP was already blocked and I have to block it again, I'll have to update the current record
			$db->updateObject('#__admintools_ipautoban', $record, array('ip'));
		}

		// Send an optional email
		if ($this->cparams->getValue('emailafteripautoban', ''))
		{
			$substitutions = $this->getEmailVariables(
				Language::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_IPAUTOBAN'),
				['[UNTIL]' => $minDate,]
			);

			// Let's get the most suitable email template
			$template = $this->getEmailTemplate('ipautoban');

			// Got no template, the user didn't published any email template, or the template doesn't want us to
			// send a notification email. Anyway, let's stop here.
			if (!$template)
			{
				return;
			}

			$subject = $template[0];
			$body    = $template[1];

			foreach ($substitutions as $k => $v)
			{
				$subject = str_replace($k, $v, $subject);
				$body    = str_replace($k, $v, $body);
			}

			$recipients = explode(',', $this->cparams->getValue('emailafteripautoban', ''));
			$recipients = array_map('trim', $recipients);

			Wordpress::sendEmail($recipients, $subject, $body);
		}
	}

	/**
	 * Gets the email template for a specific security exception reason
	 *
	 * @param   string $reason The security exception reason for which to fetch the email template
	 *
	 * @return  array
	 */
	public function getEmailTemplate($reason)
	{
		// Let's get the subject and the body from email templates
		$db        = Wordpress::getDb();

		// TODO Add support for multilanguage sites
		$languages = array($db->q('*'), $db->q('en-GB'));
		$stack     = array();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__admintools_waftemplates'))
			->where($db->qn('reason') . ' IN(' . $db->q($reason) . ', ' . $db->q('all') . ')')
			->where($db->qn('language') . ' IN(' . implode(',', $languages) . ')')
			->where($db->qn('enabled') . ' = ' . $db->q('1'));

		try
		{
			$templates = $db->setQuery($query)->loadObjectList();
		}
		catch (Exception $e)
		{
			return array();
		}

		foreach ($templates as $template)
		{
			$score = 0;

			if ($template->reason == $reason)
			{
				$score += 10;
			}

			if ($template->language == '*')
			{
				$score += 5;
			}
			elseif ($template->language == 'en-GB')
			{
				$score += 1;
			}

			$stack[$score] = $template;
		}

		ksort($stack);
		$best = array_pop($stack);

		if (!$best)
		{
			return array();
		}

		if ($this->cparams->getValue('email_throttle', 1))
		{
			// Ok I found out the best template, HOWEVER, should I really send out an email? Let's do some checks vs frequency limits
			$emails       = $best->email_num ? $best->email_num : 5;
			$numfreq      = $best->email_numfreq ? $best->email_numfreq : 1;
			$frequency    = $best->email_freq ? $best->email_freq : 'hour';
			$mindatestamp = 0;

			switch ($frequency)
			{
				case 'second':
					break;

				case 'minute':
					$numfreq *= 60;
					break;

				case 'hour':
					$numfreq *= 3600;
					break;

				case 'day':
					$numfreq *= 86400;
					break;

				case 'ever':
					$mindatestamp = 946706400; // January 1st, 2000
					break;
			}

			$now= new DateTime('now', new DateTimeZone('GMT'));

			if ($mindatestamp == 0)
			{
				$mindatestamp = $now->getTimestamp() - $numfreq;
			}

			$minDate = new DateTime('now', new DateTimeZone('GMT'));
			$minDate->setTimestamp($mindatestamp);
			$minDate = $minDate->format('Y-m-d H:i:s');

			$sql = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->qn('#__admintools_log'))
				->where($db->qn('logdate') . ' >= ' . $db->q($minDate))
				->where($db->qn('reason') . ' = ' . $db->q($reason));
			$db->setQuery($sql);
			try
			{
				$numOffenses = $db->loadResult();
			}
			catch (Exception $e)
			{
				$numOffenses = 0;
			}

			if ($numOffenses > $emails)
			{
				return array();
			}
		}

		// Because SpamAssassin is a piece of shit that blacklists our domain when it misidentifies an email as spam.
		$replaceThat = array(
			'<p style=\"text-align: right; font-size: 7pt; color: #ccc;\">Powered by <a style=\"color: #ccf; text-decoration: none;\" href=\"https://www.akeeba.com/products/admin-tools.html\">Akeeba AdminTools</a></p>',
			'<p style=\"text-align: right; font-size: 7pt; color: #ccc;\">Powered by <a style=\"color: #ccf; text-decoration: none;\" href=\"https://www.akeeba.com/products/admin-tools.html\">Akeeba AdminTools</a></p>',
			'https://www.akeeba.com',
			'https://www.akeeba.com',
			'http://akeebabackup.com',
			'https://akeebabackup.com',
			'www.akeebabackup.com',
			'www.akeeba.com',
			'akeebabackup.com',
			'akeeba.com',
		);

		foreach ($replaceThat as $find)
		{
			$best->subject  = str_ireplace($find, '', $best->subject);
			$best->template = str_ireplace($find, '', $best->template);
		}

		// Because SpamAssassin demands there is a body and surrounding html tag even though it's not necessary.
		if (strpos($best->template, '<body') == false)
		{
			$best->template = '<body>' . $best->template . '</body>';
		}

		if (strpos($best->template, '<html') == false)
		{
			$best->template = <<< SPAMASSASSINSUCKS
<html>
<head>
<title>{$best->subject}</title>
</head>
$best->template
</html>
SPAMASSASSINSUCKS;

		}

		// And now return the template
		return array(
			$best->subject,
			$best->template
		);
	}

	/**
	 * Flag security exceptions coming from private network IPs so we can notify the user
	 *
	 * @return  void
	 *
	 * @since   1.3.0
	 */
	private function flagPrivateNetworkIPs()
	{
		// I'll use the Container so I can easily set the flag and then save back to the database
		$params = $this->params;

		// Run the check only if IP workarounds are off AND the flag is set to 0 (ie not detected)
		// There's no need to run this check if the user decided to ignore the warning (value: -1) or we already detected something (value: 1)
		if (($this->cparams->getValue('ipworkarounds', -1) == -1) || ($params->getValue('detected_exceptions_from_private_network', 0) != 0))
		{
			return;
		}

		$privateNetwork = array(
			'10.0.0.0-10.255.255.255',
			'172.16.0.0-172.31.255.255',
			'192.168.0.0-192.168.255.255'
		);

		if (!Ip::IPinList(Ip::getIp(), $privateNetwork))
		{
			return;
		}

		// This IP belongs to a private network, let's raise the flag and then notify the user
		$params->setValue('detected_exceptions_from_private_network', 1);

		try
		{
			$params->save();
		}
		catch (Exception $e)
		{
			// Ignore any failures, they are not show stoppers
		}
	}
}
