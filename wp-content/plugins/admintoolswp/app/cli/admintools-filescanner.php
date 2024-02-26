<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

// Define ourselves as a parent file
define('WPINC', 1);
define('ADMINTOOLSINC', 1);

// Setup and import the base CLI script
$minphp = '7.4.0';
$curdir = __DIR__;
$myself = basename(__FILE__);

require_once __DIR__.'/../assets/cli/base.php';

use Akeeba\AdminTools\Admin\Model\Scans;

/**
 * Admin Tools File Alteration Monitor (PHP File Change Scanner) CLI application
 */
class AdminToolsFAM extends AdmintoolsCliBase
{
	/**
	 * The main entry point of the application
	 */
	public function execute()
	{
		$debugmessage = '';

		if ($this->input->get('debug', -1, 'int') != -1)
		{
			if (!defined('AKEEBADEBUG'))
			{
				define('AKEEBADEBUG', 1);
			}

			$debugmessage = "*** DEBUG MODE ENABLED ***\n";
			ini_set('display_errors', 1);
		}

		$version		 = ADMINTOOLSWP_VERSION;
		$date			 = ADMINTOOLSWP_DATE;

		$phpversion		 = PHP_VERSION;
		$phpenvironment	 = PHP_SAPI;

		$verboseMode = $this->input->get('quiet', -1, 'int') == -1;

		if ($verboseMode)
		{
			$year   = gmdate('Y');
			$header = <<<ENDBLOCK
Admin Tools PHP File Scanner CLI $version ($date)
Copyright (c) 2010-$year Akeeba Ltd / Nicholas K. Dionysopoulos
-------------------------------------------------------------------------------
Admin Tools is Free Software, distributed under the terms of the GNU General
Public License version 3 or, at your option, any later version.
This program comes with ABSOLUTELY NO WARRANTY as per sections 15 & 16 of the
license. See http://www.gnu.org/licenses/gpl-3.0.html for details.
-------------------------------------------------------------------------------
You are using PHP $phpversion ($phpenvironment)
$debugmessage


ENDBLOCK;
			$this->out($header);
		}

		$start_scan = time();

		// Attempt to use an infinite time limit, in case you are using the PHP CGI binary instead
		// of the PHP CLI binary. This will not work with Safe Mode, though.
		$safe_mode = true;

		if (function_exists('ini_get'))
		{
			$safe_mode = ini_get('safe_mode');
		}

		if (!$safe_mode && function_exists('set_time_limit'))
		{
			if ($verboseMode)
			{
				$this->out("Unsetting time limit restrictions.");
			}

			@set_time_limit(0);
		}
		elseif (!$safe_mode)
		{
			if ($verboseMode)
			{
				$this->out("Could not unset time limit restrictions; you may get a timeout error");
			}
		}
		else
		{
			if ($verboseMode)
			{
				$this->out("You are using PHP's Safe Mode; you may get a timeout error");
			}
		}

		if ($verboseMode)
		{
			$this->out('');
		}

		$model = new Scans($this->input);
		$model->removeIncompleteScans();

		$this->out("Starting file scanning");
		$this->out("");

		$warnings_flag = false;
		$ret           = $model->startScan('cli');

		while ($ret['status'] && !$ret['done'] && empty($ret['error']))
		{
			$time         = date('Y-m-d H:i:s \G\M\TO (T)');
			$memusage     = $this->memUsage();
			$warnings     = "no warnings issued (good)";
			$stepWarnings = false;

			if (!empty($ret['warnings']))
			{
				$warnings_flag = true;
				$stepWarnings  = true;

				$warnings = sprintf("POTENTIAL PROBLEMS DETECTED; %s warnings issued (see below).\n", count($ret['warnings']));

				foreach ($ret['Warnings'] as $line)
				{
					$warnings .= "\t$line\n";
				}
			}


			if (($verboseMode) || $stepWarnings)
			{
				$stepInfo = <<<ENDSTEPINFO
Last Tick   : $time
Memory used : $memusage
Warnings    : $warnings

ENDSTEPINFO;
				$this->out($stepInfo);
			}

			$ret = $model->stepScan();
		}

		if (!empty($ret['error']))
		{
			$this->out('An error has occurred:');
			$this->out($ret['error']);
			$this->out();

			$exitCode = 2;
		}
		else
		{
			if ($verboseMode)
			{
				$this->out(sprintf("File scanning job finished successfully after approximately %s", $this->timeago($start_scan, time(), '', false)));
			}

			$exitCode = 0;
		}

		if ($warnings_flag)
		{
			$exitCode = 1;

			if ($verboseMode)
			{
				$exitCode = 1;
				$this->out('');
				$this->out(str_repeat('=', 79));
				$this->out('');
				$this->out('!!!!!  W A R N I N G  !!!!!');
				$this->out('');
				$this->out('Admin Tools issued warnings during the scanning process. You have to review them');
				$this->out('and make sure that your scan has completed successfully.');
				$this->out('');
				$this->out(str_repeat('=', 79));
				$this->out('');
			}
		}

		if ($verboseMode)
		{
			$this->out(sprintf("Peak memory usage: %s", $this->peakMemUsage()));
			$this->out();
		}

		$this->close($exitCode);
	}
}

// Load the version file
require_once ADMINTOOLSWP_PATH . '/version.php';

AdmintoolsCliBase::getInstance('AdminToolsFAM')->execute();
