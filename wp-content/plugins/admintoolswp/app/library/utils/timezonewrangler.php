<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Library\Utils;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Wordpress;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * A helper class to wrangle timezones
 *
 */
class TimezoneWrangler
{
	/**
	 * The default timestamp format string to use when one is not provided
	 *
	 * @var   string
	 */
	protected $defaultFormat = 'Y-m-d H:i:s T';

	/**
	 * When set, this timezone will be used instead of the Joomla! applicable timezone for the user.
	 *
	 * @var DateTimeZone
	 */
	protected $forcedTimezone = null;

	/**
	 * Get the default timestamp format to use when one is not provided
	 *
	 * @return  string
	 */
	public function getDefaultFormat()
	{
		return $this->defaultFormat;
	}

	/**
	 * Set the default timestamp format to use when one is not provided
	 *
	 * @param   string  $defaultFormat
	 *
	 * @return  void
	 */
	public function setDefaultFormat($defaultFormat)
	{
		$this->defaultFormat = $defaultFormat;
	}

	/**
	 * Returns the forced timezone which is used instead of the applicable Joomla! timezone.
	 *
	 * @return  DateTimeZone
	 */
	public function getForcedTimezone()
	{
		return $this->forcedTimezone;
	}

	/**
	 * Sets the forced timezone which is used instead of the applicable Joomla! timezone. If the new timezone is
	 * different than the existing one we will also reset the user to timezone cache.
	 *
	 * @param   DateTimeZone|string  $forcedTimezone
	 *
	 * @return  void
	 */
	public function setForcedTimezone($forcedTimezone)
	{
		// Are we unsetting the forced TZ?
		if (empty($forcedTimezone))
		{
			$this->forcedTimezone = null;

			return;
		}

		// If the new TZ is a string we have to create an object
		if (is_string($forcedTimezone))
		{
			$forcedTimezone = new DateTimeZone($forcedTimezone);
		}

		$oldTZ = '';

		if (is_object($this->forcedTimezone) && ($this->forcedTimezone instanceof DateTimeZone))
		{
			$oldTZ = $this->forcedTimezone->getName();
		}

		if ($oldTZ == $forcedTimezone->getName())
		{
			return;
		}

		$this->forcedTimezone = $forcedTimezone;
	}

	/**
	 * Returns the forced timezone or fetches it from the configuration
	 *
	 * @return  DateTimeZone
	 */
	public function getApplicableTimezone()
	{
		// If we have a forced timezone use it instead of trying to figure anything out.
		if (is_object($this->forcedTimezone))
		{
			return $this->forcedTimezone;
		}

		// Get the Server Timezone from Global Configuration with a fallback to UTC
		$tz = Wordpress::get_timezone_string();

		try
		{
			$this->forcedTimezone = new DateTimeZone($tz);
		}
		catch (Exception $e)
		{
			// If an invalid timezone was set we get to use UTC
			$this->forcedTimezone = new DateTimeZone('UTC');
		}

		return $this->forcedTimezone;
	}

	/**
	 * Returns a FOF Date object with its timezone set to the user's applicable timezone.
	 *
	 * If no user is specified the current user will be used.
	 *
	 * $time can be a DateTime object (including Date and JDate), an integer (UNIX timestamp) or a date string. If no
	 * timezone is specified in a date string we assume it's GMT.
	 *
	 * @param   mixed  $time  Source time. Leave blank for current date/time.
	 *
	 * @return  DateTime
	 */
	public function getLocalDateTime($time = null)
	{
		$time = empty($time) ? 'now' : $time;
		$date = new DateTime($time);
		$tz   = $this->getApplicableTimezone();
		$date->setTimezone($tz);

		return $date;
	}

	/**
	 * Returns a DateTime object with its timezone set to UTC.
	 *
	 * $time can be a DateTime object, an integer (UNIX timestamp) or a date string. If no
	 * timezone is specified in a date string we assume it's the server timezone.
	 *
	 * @param   mixed  $time
	 *
	 * @return  DateTime
	 */
	public function getUTCDateTime($time)
	{
		$time        = empty($time) ? 'now' : $time;
		$tz          = $this->getApplicableTimezone();
		$date        = new DateTime($time, $tz);
		$gmtTimezone = new DateTimeZone('UTC');
		$date->setTimezone($gmtTimezone);

		return $date;
	}

	/**
	 * Returns a formatted date string in server timezone.
	 *
	 * If no format is specified we will use $defaultFormat.
	 *
	 * $time can be a DateTime object (including Date and JDate), an integer (UNIX timestamp) or a date string. If no
	 * timezone is specified in a date string we assume it's GMT.
	 *
	 * @param   string|null                    $format     Timestamp format. If empty $defaultFormat is used.
	 * @param   DateTime|string|int|null  $time       Source time. Leave blank for current date/time.
	 *
	 * @return  string
	 */
	public function getLocalTimeStamp($format = null, $time = null)
	{
		$date   = $this->getLocalDateTime($time);
		$format = empty($format) ? $this->defaultFormat : $format;

		return $date->format($format);
	}
}
