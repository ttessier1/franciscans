<?php
/**
 * @package   awf
 * @copyright Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Awf\Registry\Format;

use Awf\Registry\AbstractRegistryFormat;

/**
 * JSON format handler for Registry.
 *
 * This class is adapted from the Joomla! Framework
 */
class Json extends AbstractRegistryFormat
{
	/**
	 * Converts an object into a JSON formatted string.
	 *
	 * @param   object  $object   Data source object.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  string  JSON formatted string.
	 */
	public function objectToString($object, $options = array())
	{
		if (isset($options['pretty_print']) && $options['pretty_print'] && defined('JSON_PRETTY_PRINT'))
		{
			return json_encode($object, JSON_PRETTY_PRINT);
		}

		return json_encode($object);
	}

	/**
	 * Parse a JSON formatted string and convert it into an object.
	 *
	 * If the string is not in JSON format, this method will attempt to parse it as INI format.
	 *
	 * @param   string  $data     JSON formatted string to convert.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  object   Data object.
	 */
	public function stringToObject($data, array $options = array('processSections' => false))
	{
		$data = trim($data);

		if ((substr($data, 0, 1) != '{') && (substr($data, -1, 1) != '}'))
		{
			$ini = AbstractRegistryFormat::getInstance('Ini');
			$obj = $ini->stringToObject($data, $options);
		}
		else
		{
			$obj = json_decode($data);
		}

		return $obj;
	}
}
