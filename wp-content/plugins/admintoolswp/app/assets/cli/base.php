<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Input\Cli;
use Akeeba\AdminTools\Library\Input\Filter;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Registry\Registry;

// Work around some misconfigured servers which print out notices
if (function_exists('error_reporting'))
{
	$oldLevel = error_reporting(0);
}

// Minimum PHP version check
if (!isset($minphp))
{
	$minphp = '7.4.0';
}

if (version_compare(PHP_VERSION, $minphp, 'lt'))
{
	$curversion = PHP_VERSION;
	$bindir = PHP_BINDIR;
	echo <<< ENDWARNING
================================================================================
WARNING! Incompatible PHP version $curversion (required: $minphp or later)
================================================================================

This script must be run using PHP version $minphp or later. Your server is
currently using a much older version which would cause this script to crash. As
a result we have aborted execution of the script. Please contact your host and
ask them for the correct path to the PHP CLI binary for PHP $minphp or later, then
edit your CRON job and replace your current path to PHP with the one your host
gave you.

For your information, the current PHP version information is as follows.

PATH:    $bindir
VERSION: $curversion

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
IMPORTANT!
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
PHP version numbers are NOT decimals! Trailing zeros do matter. For example,
PHP 5.3.28 is twenty four versions newer (greater than) than PHP 5.3.4.
Please consult https://www.akeeba.com/how-do-version-numbers-work.html


Further clarifications:

1. There is no possible way that you are receiving this message in error. We
   are using the PHP_VERSION constant to detect the PHP version you are
   currently using. This is what PHP itself reports as its own version. It
   simply cannot lie.

2. Even though your *site* may be running in a higher PHP version that the one
   reported above, your CRON scripts will most likely not be running under it.
   This has to do with the fact that your site DOES NOT run under the command
   line and there are different executable files (binaries) for the web and
   command line versions of PHP.

3. Please note that we cannot provide support about this error as the solution
   depends only on your server setup. The only people who know how your server
   is set up are your host's technicians. Therefore we can only advise you to
   contact your host and request them the correct path to the PHP CLI binary.
   Let us stress out that only your host knows and can give this information
   to you.

4. The latest published versions of PHP can be found at http://www.php.net/
   Any older version is considered insecure and must not be used on a
   production site. If your server uses a much older version of PHP than those
   published in the URL above please notify your host that their servers are
   insecure and in need of an update.

This script will now terminate. Goodbye.

ENDWARNING;
	die();
}

// Register our variable and load the bootstrap file
if (!defined('ADMINTOOLSWP_PATH'))
{
	define('ADMINTOOLSWP_PATH', realpath(__DIR__.'/../../../'));
}

require_once ADMINTOOLSWP_PATH.'/helpers/bootstrap.php';

// Try our very best to detect WordPress root path.
// We can safely do that since WP will check if this constant is already defined
if (!defined('ABSPATH'))
{
	$root = Wordpress::getSiteRoot();
	define('ABSPATH', $root);
}

/**
 * Base class for a command line application. Adapted from JCli / JApplicationCli
 */
class AdmintoolsCliBase
{
	/**
	 * The application input object.
	 *
	 * @var    Input
	 */
	public $input;

	/**
	 * The application configuration object.
	 *
	 * @var    Registry
	 */
	protected $config;

	/**
	 * The application instance.
	 *
	 * @var    AdmintoolsCliBase
	 */
	protected static $instance;

	/**
	 * POSIX-style CLI options. Access them with through the getOption method.
	 *
	 * @var   array
	 */
	protected static $cliOptions = array();

	/**
	 * Filter object to use.
	 *
	 * @var    Filter
	 */
	protected $filter = null;

	protected function __construct()
	{
		global $argc, $argv;

		/**
		 * Do not allow the script to run under a web server.
		 *
		 * The trick is that the $argc and $argv globals are set by the CLI and CGI SAPIs when running under a command line, but
		 * not when the script runs through a web server.
		 *
		 * Failing this check, we make sure that the REQUEST_URI or HTTP_HOST environment variables are set. These are
		 * normally set only under a web server SAPI.
		 *
		 * If both checks fail we run under a web server. In this case we send an HTTP 404 header to confuse fingerprinting
		 * bots. At the same time we print an informative error message in the standard error in the off-chance that this is a
		 * false positive (and it'll give us information about what exactly happened there).
		 */
		if ( (!isset($argc) && !isset($argv)) ||
			(isset($_SERVER['REQUEST_URI']) || isset($_SERVER['HTTP_HOST']))
		)
		{
			if (!isset($myself))
			{
				$myself     = basename($_SERVER['REQUEST_URI']);
			}

			$sapi       = PHP_SAPI;
			$phpVersion = PHP_VERSION;
			fputs(STDERR, "The $myself script is meant to be run from a CLI version of PHP. You are currently using the $sapi version of PHP $phpVersion.");
			header('HTTP/1.1 404 Not Found');

			return;
		}

		$cgiMode = false;

		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$cgiMode = true;
		}

		// Create a Input object
		if ($cgiMode)
		{
			$query = "";
			if (!empty($_GET))
			{
				foreach ($_GET as $k => $v)
				{
					$query .= " $k";
					if ($v != "")
					{
						$query .= "=$v";
					}
				}
			}
			$query	 = ltrim($query);
			$argv	 = explode(' ', $query);
			$argc	 = count($argv);

			$_SERVER['argv'] = $argv;
		}

		$this->input  = new Cli();
		$this->filter = new Filter();

		// Create the registry with a default namespace of config
		$this->config = new Registry();

		// Set the execution datetime and timestamp;
		$this->config->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->config->set('execution.timestamp', time());

		// Set the current directory.
		$this->config->set('cwd', getcwd());

		// Parse the POSIX options
		$this->parseOptions();
	}

	/**
	 * Returns a reference to the global AdmintoolsCliBase object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as: $cli = AdmintoolsCliBase::getInstance();
	 *
	 * @param   string $name The name of the AdmintoolsCliBase class to instantiate.
	 *
	 * @return  AdmintoolsCliBase  A AdmintoolsCliBase object
	 */
	public static function &getInstance($name = null)
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
			if (class_exists($name) && (is_subclass_of($name, 'AdmintoolsCliBase')))
			{
				self::$instance = new $name;
			}
			else
			{
				self::$instance = new AdmintoolsCliBase;
			}
		}

		return self::$instance;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 */
	public function execute()
	{
		$this->close();
	}

	/**
	 * Exit the application.
	 *
	 * @param   integer $code Exit code.
	 *
	 * @return  void
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Load an object or array into the application configuration object.
	 *
	 * @param   mixed $data Either an array or object to be loaded into the configuration object.
	 *
	 * @return  void
	 */
	public function loadConfiguration($data)
	{
		// Load the data into the configuration object.
		if (is_array($data))
		{
			$this->config->loadArray($data);
		}
		elseif (is_object($data))
		{
			$this->config->loadObject($data);
		}
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string  $text The text to display.
	 * @param   boolean $nl   True to append a new line at the end of the output string.
	 *
	 * @return  void
	 */
	public function out($text = '', $nl = true)
	{
		fwrite(STDOUT, $text . ($nl ? "\n" : null));
	}

	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 */
	public function in()
	{
		return rtrim(fread(STDIN, 8192), "\n");
	}

	/**
	 * Returns a fancy formatted time lapse code
	 *
	 * @param   int     $referencedate  Timestamp of the reference date/time
	 * @param   string  $timepointer    Timestamp of the current date/time
	 * @param   string  $measureby	    Time unit. One of s, m, h, d, or y.
	 * @param   bool    $autotext       Add "ago" / "from now" suffix?
	 *
	 * @return  string
	 */
	protected function timeago($referencedate = 0, $timepointer = '', $measureby = '', $autotext = true)
	{
		if ($timepointer == '')
		{
			$timepointer = time();
		}

		// Raw time difference
		$Raw	 = $timepointer - $referencedate;
		$Clean	 = abs($Raw);

		$calcNum = array(
			array('s', 60),
			array('m', 60 * 60),
			array('h', 60 * 60 * 60),
			array('d', 60 * 60 * 60 * 24),
			array('y', 60 * 60 * 60 * 24 * 365)
		);

		$calc = array(
			's'	 => array(1, 'second'),
			'm'	 => array(60, 'minute'),
			'h'	 => array(60 * 60, 'hour'),
			'd'	 => array(60 * 60 * 24, 'day'),
			'y'	 => array(60 * 60 * 24 * 365, 'year')
		);

		if ($measureby == '')
		{
			$usemeasure = 's';

			for ($i = 0; $i < count($calcNum); $i++)
			{
				if ($Clean <= $calcNum[$i][1])
				{
					$usemeasure	 = $calcNum[$i][0];
					$i			 = count($calcNum);
				}
			}
		}
		else
		{
			$usemeasure = $measureby;
		}

		$datedifference = floor($Clean / $calc[$usemeasure][0]);

		if ($autotext == true && ($timepointer == time()))
		{
			if ($Raw < 0)
			{
				$prospect = ' from now';
			}
			else
			{
				$prospect = ' ago';
			}
		}
		else
		{
			$prospect = '';
		}

		if ($referencedate != 0)
		{
			if ($datedifference == 1)
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . ' ' . $prospect;
			}
			else
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . 's ' . $prospect;
			}
		}
		else
		{
			return 'No input time referenced.';
		}
	}

	/**
	 * Formats a number of bytes in human readable format
	 *
	 * @param   int  $size  The size in bytes to format, e.g. 8254862
	 *
	 * @return  string  The human-readable representation of the byte size, e.g. "7.87 Mb"
	 */
	protected function formatByteSize($size)
	{
		$unit	 = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
		return @round($size / pow(1024, ($i	= floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
	}

	/**
	 * Returns the current memory usage, formatted
	 *
	 * @return  string
	 */
	protected function memUsage()
	{
		if (function_exists('memory_get_usage'))
		{
			$size	 = memory_get_usage();
			return $this->formatByteSize($size);
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Returns the peak memory usage, formatted
	 *
	 * @return  string
	 */
	protected function peakMemUsage()
	{
		if (function_exists('memory_get_peak_usage'))
		{
			$size	 = memory_get_peak_usage();
			return $this->formatByteSize($size);
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Parses POSIX command line options and sets the self::$cliOptions associative array. Each array item contains
	 * a single dimensional array of values. Arguments without a dash are silently ignored.
	 *
	 * This works much better than JInputCli since it allows you to use all POSIX-valid ways of defining CLI parameters.
	 *
	 * @return  void
	 */
	protected function parseOptions()
	{
		global $argc, $argv;

		// Workaround for PHP-CGI
		if (!isset($argc) && !isset($argv))
		{
			$query = "";

			if (!empty($_GET))
			{
				foreach ($_GET as $k => $v)
				{
					$query .= " $k";

					if ($v != "")
					{
						$query .= "=$v";
					}
				}
			}

			$query = ltrim($query);
			$argv  = explode(' ', $query);
			$argc  = count($argv);
		}

		$currentName = "";
		$options     = array();

		for ($i = 1; $i < $argc; $i++)
		{
			$argument = $argv[ $i ];

			$value = $argument;

			if (strpos($argument, "-") === 0)
			{
				$argument = ltrim($argument, '-');

				$name  = $argument;
				$value = null;

				if (strstr($argument, '='))
				{
					list($name, $value) = explode('=', $argument, 2);
				}

				$currentName = $name;

				if (!isset($options[ $currentName ]) || ($options[ $currentName ] == null))
				{
					$options[ $currentName ] = array();
				}
			}

			if ((!is_null($value)) && (!is_null($currentName)))
			{
				$key = null;

				if (strstr($value, '='))
				{
					$parts = explode('=', $value, 2);
					$key   = $parts[0];
					$value = $parts[1];
				}

				$values = $options[ $currentName ];

				if (is_null($values))
				{
					$values = array();
				}

				if (is_null($key))
				{
					array_push($values, $value);
				}
				else
				{
					$values[ $key ] = $value;
				}

				$options[ $currentName ] = $values;
			}
		}

		self::$cliOptions = $options;
	}

	/**
	 * Returns the value of a command line option
	 *
	 * @param   string  $key      The full name of the option, e.g. "foobar"
	 * @param   mixed   $default  The default value to return
	 * @param   string  $type     Filter type, e.g. cmd, int, bool and so on.
	 *
	 * @return  mixed  The value of the option
	 */
	protected function getOption($key, $default = null, $type = 'raw')
	{
		// If the key doesn't exist set it to the default value
		if (!array_key_exists($key, self::$cliOptions))
		{
			self::$cliOptions[$key] = is_array($default) ? $default : array($default);
		}

		$type = strtolower($type);

		if ($type == 'array')
		{
			return self::$cliOptions[$key];
		}

		return $this->filterVariable(self::$cliOptions[$key][0], $type);
	}

	protected function filterVariable($var, $type = 'cmd')
	{
		return $this->filter->clean($var, $type);
	}
}

/**
 * @param   Throwable  $ex  The Exception / Error being handled
 */
function akeeba_exception_handler($ex)
{
	echo "\n\n";
	echo "********** ERROR! **********\n\n";
	echo $ex->getMessage();
	echo "\n\nTechnical information:\n\n";
	echo "Code: " . $ex->getCode() . "\n";
	echo "File: " . $ex->getFile() . "\n";
	echo "Line: " . $ex->getLine() . "\n";
	echo "\nStack Trace:\n\n" . $ex->getTraceAsString();
	die("\n\n");
}

/**
 * Timeout handler
 *
 * This function is registered as a shutdown script. If a catchable timeout occurs it will detect it and print a helpful
 * error message instead of just dying cold.
 *
 * @return  void
 */
function akeeba_timeout_handler()
{
	$connection_status = connection_status();

	if ($connection_status == 0)
	{
		// Normal script termination, do not report an error.
		return;
	}

	echo "\n\n";
	echo "********** ERROR! **********\n\n";

	if ($connection_status == 1)
	{
		echo <<< END
The process was aborted on user's request.

This usually means that you pressed CTRL-C to terminate the script (if you're
running it from a terminal / SSH session), or that your host's CRON daemon
aborted the execution of this script.

If you are running this script through a CRON job and saw this message, please
contact your host and request an increase in the timeout limit for CRON jobs.
Moreover you need to ask them to increase the max_execution_time in the
php.ini file or, even better, set it to 0.
END;
	}
	else
	{
		echo <<< END
This script has timed out. As a result, the process has FAILED to complete.

Your host applies a maximum execution time for CRON jobs which is too low for
this script to work properly. Please contact your host and request an increase
in the timeout limit for CRON jobs. Moreover you need to ask them to increase
the max_execution_time in the php.ini file or, even better, set it to 0.
END;


		if (!function_exists('php_ini_loaded_file'))
		{
			echo "\n\n";

			return;
		}

		$ini_location = php_ini_loaded_file();

		echo <<<END
The php.ini file your host will need to modify is located at:
$ini_location
Info for the host: the location above is reported by PHP's php_ini_loaded_file() method.

END;

		die("\n\n");}
}

/**
 * Error handler. It tries to catch fatal errors and report them in a meaningful way. Obviously it only works for
 * catchable fatal errors...
 *
 * @param   int     $errno    Error number
 * @param   string  $errstr   Error string, tells us what went wrong
 * @param   string  $errfile  Full path to file where the error occurred
 * @param   int     $errline  Line number where the error occurred
 *
 * @return  void
 */
function akeeba_error_handler($errno, $errstr, $errfile, $errline)
{
	switch ($errno)
	{
		case E_ERROR:
		case E_USER_ERROR:
			echo "\n\n";
			echo "********** ERROR! **********\n\n";
			echo "PHP Fatal Error: $errstr";
			echo "\n\nTechnical information:\n\n";
			echo "File: " . $errfile . "\n";
			echo "Line: " . $errline . "\n";
			echo "\nStack Trace:\n\n" . debug_backtrace();
			die("\n\n");
			break;

		default:
			break;
	}
}

set_exception_handler('akeeba_exception_handler');
set_error_handler('akeeba_error_handler', E_ERROR | E_USER_ERROR);
register_shutdown_function('akeeba_timeout_handler');
