<?php
/**
 * @package   awf
 * @copyright Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Awf\Date;
use Awf\Application\Application;
use Awf\Container\Container;
use Awf\Container\ContainerAwareInterface;
use Awf\Container\ContainerAwareTrait;
use Awf\Database\Driver;
use Awf\Exception\App;

/**
 * Date is a class that stores a date and provides logic to manipulate
 * and render that date in a variety of formats.
 *
 * @property-read  string   $daysinmonth   t - Number of days in the given month.
 * @property-read  string   $dayofweek     N - ISO-8601 numeric representation of the day of the week.
 * @property-read  string   $dayofyear     z - The day of the year (starting from 0).
 * @property-read  boolean  $isleapyear    L - Whether it's a leap year.
 * @property-read  string   $day           d - Day of the month, 2 digits with leading zeros.
 * @property-read  string   $hour          H - 24-hour format of an hour with leading zeros.
 * @property-read  string   $minute        i - Minutes with leading zeros.
 * @property-read  string   $second        s - Seconds with leading zeros.
 * @property-read  string   $month         m - Numeric representation of a month, with leading zeros.
 * @property-read  string   $ordinal       S - English ordinal suffix for the day of the month, 2 characters.
 * @property-read  string   $week          W - Numeric representation of the day of the week.
 * @property-read  string   $year          Y - A full numeric representation of a year, 4 digits.
 *
 * This class is adapted from the Joomla! Framework
 */
class Date extends \DateTime implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The format string to be applied when using the __toString() magic method.
	 *
	 * @var    string
	 */
	public static $format = 'Y-m-d H:i:s';

	/**
	 * Placeholder for a DateTimeZone object with GMT as the time zone.
	 *
	 * @var    \DateTimeZone
	 */
	protected static $gmt;

	/**
	 * Placeholder for a DateTimeZone object with the default server
	 * time zone as the time zone.
	 *
	 * @var    \DateTimeZone
	 */
	protected static $stz;

	/**
	 * The DateTimeZone object for usage in rending dates as strings.
	 *
	 * @var    \DateTimeZone
	 */
	protected $tz;

	/**
	 * Constructor.
	 *
	 * @param   string          $date       String in a format accepted by strtotime(), defaults to "now".
	 * @param   mixed           $tz         Time zone to be used for the date. Might be a string or a DateTimeZone
	 *                                      object.
	 * @param   Container|null  $container  The DI Container of the application
	 *
	 * @throws App
	 */
	public function __construct($date = 'now', $tz = null, ?Container $container = null)
	{
		/** @deprecated 2.0 The container argument will become mandatory */
		if (empty($container))
		{
			trigger_error(
				sprintf('The container argument is mandatory in %s', __METHOD__),
				E_USER_DEPRECATED
			);

			$container = Application::getInstance()->getContainer();
		}

		$this->setContainer($container);

		// Create the base GMT and server time zone objects.
		if (empty(self::$gmt) || empty(self::$stz))
		{
			self::$gmt = new \DateTimeZone('GMT');
			self::$stz = new \DateTimeZone(@date_default_timezone_get());
		}

		// If the time zone object is not set, attempt to build it.
		if (!($tz instanceof \DateTimeZone))
		{
			if ($tz === null)
			{
				$tz = self::$gmt;
			}
			elseif (is_string($tz))
			{
				$tz = new \DateTimeZone($tz);
			}
		}

		// If the date is numeric assume a unix timestamp and convert it.
		date_default_timezone_set('UTC');
		$date = is_numeric($date) ? date('c', $date) : $date;

		// Call the DateTime constructor.
		parent::__construct($date, $tz);

		// Reset the timezone for 3rd party libraries/extension that does not use JDate
		date_default_timezone_set(self::$stz->getName());

		// Set the timezone object for access later.
		$this->tz = $tz;
	}

	/**
	 * Magic method to access properties of the date given by class to the format method.
	 *
	 * @param   string  $name  The name of the property.
	 *
	 * @return  mixed   A value if the property name is valid, null otherwise.
	 */
	public function __get($name)
	{
		$value = null;

		switch ($name)
		{
			case 'daysinmonth':
				$value = $this->format('t', true);
				break;

			case 'dayofweek':
				$value = $this->format('N', true);
				break;

			case 'dayofyear':
				$value = $this->format('z', true);
				break;

			case 'isleapyear':
				$value = (boolean) $this->format('L', true);
				break;

			case 'day':
				$value = $this->format('d', true);
				break;

			case 'hour':
				$value = $this->format('H', true);
				break;

			case 'minute':
				$value = $this->format('i', true);
				break;

			case 'second':
				$value = $this->format('s', true);
				break;

			case 'month':
				$value = $this->format('m', true);
				break;

			case 'ordinal':
				$value = $this->format('S', true);
				break;

			case 'week':
				$value = $this->format('W', true);
				break;

			case 'year':
				$value = $this->format('Y', true);
				break;

			default:
				$trace = debug_backtrace();
				trigger_error(
					'Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
					E_USER_NOTICE
				);
		}

		return $value;
	}

	/**
	 * Magic method to render the date object in the format specified in the public
	 * static member Date::$format.
	 *
	 * @return  string  The date as a formatted string.
	 */
	public function __toString()
	{
		return (string) parent::format(self::$format);
	}

	/**
	 * Gets the date as a formatted string.
	 *
	 * @param   string   $format  The date format specification string (see {@link PHP_MANUAL#date})
	 * @param   boolean  $local   True to return the date string in the local time zone, false to return it in GMT.
	 *
	 * @return  string   The date string in the specified format format.
	 */
	#[\ReturnTypeWillChange]
	public function format($format, $local = false)
	{
		// If the returned time should not be local use GMT.
		if ($local == false)
		{
			parent::setTimezone(self::$gmt);
		}

		// Format the date.
		$return = parent::format($format);

		if ($local == false)
		{
			parent::setTimezone($this->tz);
		}

		return $return;
	}

	/**
	 * Get the time offset from GMT in hours or seconds.
	 *
	 * @param   boolean  $hours  True to return the value in hours.
	 *
	 * @return  float  The time offset from GMT either in hours or in seconds.
	 */
	public function getOffsetFromGMT($hours = false)
	{
		return (float) $hours ? ($this->tz->getOffset($this) / 3600) : $this->tz->getOffset($this);
	}

	/**
	 * Method to wrap the setTimezone() function and set the internal time zone object.
	 *
	 * @param   \DateTimeZone  $tz  The new DateTimeZone object.
	 *
	 * @return  Date
	 *
	 * @note    This method can't be type hinted due to a PHP bug: https://bugs.php.net/bug.php?id=61483
	 */
	#[\ReturnTypeWillChange]
	public function setTimezone($tz)
	{
		$this->tz = $tz;

		return parent::setTimezone($tz);
	}

	/**
	 * Gets the date as an ISO 8601 string.  IETF RFC 3339 defines the ISO 8601 format
	 * and it can be found at the IETF Web site.
	 *
	 * @param   boolean  $local  True to return the date string in the local time zone, false to return it in GMT.
	 *
	 * @return  string  The date string in ISO 8601 format.
	 *
	 * @link    http://www.ietf.org/rfc/rfc3339.txt
	 */
	public function toISO8601($local = false)
	{
		return $this->format(\DateTime::RFC3339, $local);
	}

	/**
	 * Gets the date as an RFC 822 string.  IETF RFC 2822 supercedes RFC 822 and its definition
	 * can be found at the IETF Web site.
	 *
	 * @param   boolean  $local  True to return the date string in the local time zone, false to return it in GMT.
	 *
	 * @return  string   The date string in RFC 822 format.
	 *
	 * @link    http://www.ietf.org/rfc/rfc2822.txt
	 */
	public function toRFC822($local = false)
	{
		return $this->format(\DateTime::RFC2822, $local);
	}

	/**
	 * Gets the date as UNIX time stamp.
	 *
	 * @return  integer  The date as a UNIX timestamp.
	 */
	public function toUnix()
	{
		return (int) parent::format('U');
	}

	/**
	 * Gets the date as an SQL datetime string.
	 *
	 * @param   boolean  $local  True to return the date string in the local time zone, false to return it in GMT.
	 * @param   Driver   $db     The database driver or null to use JFactory::getDbo()
	 *
	 * @return  string  The date string in SQL datetime format.
	 *
	 * @link    http://dev.mysql.com/doc/refman/5.0/en/datetime.html
	 */
	public function toSql($local = false, Driver $db = null)
	{
		$db = $db ?? $this->container->db;

		return $this->format($db->getDateFormat(), $local);
	}
}
