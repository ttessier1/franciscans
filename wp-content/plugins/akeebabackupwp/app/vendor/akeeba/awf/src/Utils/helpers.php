<?php
/**
 * @package   awf
 * @copyright Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/**
 * This is a modified copy of Laravel 4's "helpers.php"
 *
 * Laravel 4 is distributed under the MIT license, see https://github.com/laravel/framework/blob/master/LICENSE.txt
 */

if ( ! function_exists('akeeba_array_add'))
{
	/**
	 * Add an element to an array if it doesn't exist.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	function akeeba_array_add($array, $key, $value)
	{
		if ( ! isset($array[$key])) $array[$key] = $value;

		return $array;
	}
}

if ( ! function_exists('akeeba_array_build'))
{
	/**
	 * Build a new array using a callback.
	 *
	 * @param  array  $array
	 * @param  \Closure  $callback
	 * @return array
	 */
	function akeeba_array_build($array, Closure $callback)
	{
		$results = array();

		foreach ($array as $key => $value)
		{
			list($innerKey, $innerValue) = call_user_func($callback, $key, $value);

			$results[$innerKey] = $innerValue;
		}

		return $results;
	}
}

if ( ! function_exists('akeeba_array_divide'))
{
	/**
	 * Divide an array into two arrays. One with keys and the other with values.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function akeeba_array_divide($array)
	{
		return array(array_keys($array), array_values($array));
	}
}

if ( ! function_exists('akeeba_array_dot'))
{
	/**
	 * Flatten a multi-dimensional associative array with dots.
	 *
	 * @param  array   $array
	 * @param  string  $prepend
	 * @return array
	 */
	function akeeba_array_dot($array, $prepend = '')
	{
		$results = array();

		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$results = array_merge($results, akeeba_array_dot($value, $prepend.$key.'.'));
			}
			else
			{
				$results[$prepend.$key] = $value;
			}
		}

		return $results;
	}
}

if ( ! function_exists('akeeba_array_except'))
{
	/**
	 * Get all of the given array except for a specified array of items.
	 *
	 * @param  array  $array
	 * @param  array  $keys
	 * @return array
	 */
	function akeeba_array_except($array, $keys)
	{
		return array_diff_key($array, array_flip((array) $keys));
	}
}

if ( ! function_exists('akeeba_array_fetch'))
{
	/**
	 * Fetch a flattened array of a nested array element.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @return array
	 */
	function akeeba_array_fetch($array, $key)
	{
		foreach (explode('.', $key) as $segment)
		{
			$results = array();

			foreach ($array as $value)
			{
				$value = (array) $value;

				$results[] = $value[$segment];
			}

			$array = array_values($results);
		}

		return array_values($results);
	}
}

if ( ! function_exists('akeeba_array_first'))
{
	/**
	 * Return the first element in an array passing a given truth test.
	 *
	 * @param  array    $array
	 * @param  Closure  $callback
	 * @param  mixed    $default
	 * @return mixed
	 */
	function akeeba_array_first($array, $callback, $default = null)
	{
		foreach ($array as $key => $value)
		{
			if (call_user_func($callback, $key, $value)) return $value;
		}

		return akeeba_value($default);
	}
}

if ( ! function_exists('akeeba_array_last'))
{
	/**
	 * Return the last element in an array passing a given truth test.
	 *
	 * @param  array    $array
	 * @param  Closure  $callback
	 * @param  mixed    $default
	 * @return mixed
	 */
	function akeeba_array_last($array, $callback, $default = null)
	{
		return akeeba_array_first(array_reverse($array), $callback, $default);
	}
}

if ( ! function_exists('akeeba_array_flatten'))
{
	/**
	 * Flatten a multi-dimensional array into a single level.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function akeeba_array_flatten($array)
	{
		$return = array();

		array_walk_recursive($array, function($x) use (&$return) { $return[] = $x; });

		return $return;
	}
}

if ( ! function_exists('akeeba_array_forget'))
{
	/**
	 * Remove an array item from a given array using "dot" notation.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @return void
	 */
	function akeeba_array_forget(&$array, $key)
	{
		$keys = explode('.', $key);

		while (count($keys) > 1)
		{
			$key = array_shift($keys);

			if ( ! isset($array[$key]) || ! is_array($array[$key]))
			{
				return;
			}

			$array =& $array[$key];
		}

		unset($array[array_shift($keys)]);
	}
}

if ( ! function_exists('akeeba_array_get'))
{
	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function akeeba_array_get($array, $key, $default = null)
	{
		if (is_null($key)) return $array;

		if (isset($array[$key])) return $array[$key];

		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) || ! array_key_exists($segment, $array))
			{
				return akeeba_value($default);
			}

			$array = $array[$segment];
		}

		return $array;
	}
}

if ( ! function_exists('akeeba_array_only'))
{
	/**
	 * Get a subset of the items from the given array.
	 *
	 * @param  array  $array
	 * @param  array  $keys
	 * @return array
	 */
	function akeeba_array_only($array, $keys)
	{
		return array_intersect_key($array, array_flip((array) $keys));
	}
}

if ( ! function_exists('akeeba_array_pluck'))
{
	/**
	 * Pluck an array of values from an array.
	 *
	 * @param  array   $array
	 * @param  string  $value
	 * @param  string  $key
	 * @return array
	 */
	function akeeba_array_pluck($array, $value, $key = null)
	{
		$results = array();

		foreach ($array as $item)
		{
			$itemValue = is_object($item) ? $item->{$value} : $item[$value];

			// If the key is "null", we will just append the value to the array and keep
			// looping. Otherwise we will key the array using the value of the key we
			// received from the developer. Then we'll return the final array form.
			if (is_null($key))
			{
				$results[] = $itemValue;
			}
			else
			{
				$itemKey = is_object($item) ? $item->{$key} : $item[$key];

				$results[$itemKey] = $itemValue;
			}
		}

		return $results;
	}
}

if ( ! function_exists('akeeba_array_pull'))
{
	/**
	 * Get a value from the array, and remove it.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @return mixed
	 */
	function akeeba_array_pull(&$array, $key)
	{
		$value = akeeba_array_get($array, $key);

		akeeba_array_forget($array, $key);

		return $value;
	}
}

if ( ! function_exists('akeeba_array_set'))
{
	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	function akeeba_array_set(&$array, $key, $value)
	{
		if (is_null($key)) return $array = $value;

		$keys = explode('.', $key);

		while (count($keys) > 1)
		{
			$key = array_shift($keys);

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if ( ! isset($array[$key]) || ! is_array($array[$key]))
			{
				$array[$key] = array();
			}

			$array =& $array[$key];
		}

		$array[array_shift($keys)] = $value;

		return $array;
	}
}

if ( ! function_exists('akeeba_array_sort'))
{
	/**
	 * Sort the array using the given Closure.
	 *
	 * @param  array  $array
	 * @param  \Closure  $callback
	 * @return array
	 */
	function akeeba_array_sort($array, Closure $callback)
	{
		return Awf\Utils\Collection::make($array)->sortBy($callback)->all();
	}
}

if ( ! function_exists('akeeba_array_where'))
{
	/**
	 * Filter the array using the given Closure.
	 *
	 * @param  array  $array
	 * @param  \Closure  $callback
	 * @return array
	 */
	function akeeba_array_where($array, Closure $callback)
	{
		$filtered = array();

		foreach ($array as $key => $value)
		{
			if (call_user_func($callback, $key, $value)) $filtered[$key] = $value;
		}

		return $filtered;
	}
}

if ( ! function_exists('akeeba_ends_with'))
{
	/**
	 * Determine if a given string ends with a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	function akeeba_ends_with($haystack, $needles)
	{
		foreach ((array) $needles as $needle)
		{
			if ((string) $needle === substr($haystack, -strlen($needle))) return true;
		}

		return false;
	}
}

if ( ! function_exists('akeeba_last'))
{
	/**
	 * Get the last element from an array.
	 *
	 * @param  array  $array
	 * @return mixed
	 */
	function akeeba_last($array)
	{
		return end($array);
	}
}

if ( ! function_exists('akeeba_object_get'))
{
	/**
	 * Get an item from an object using "dot" notation.
	 *
	 * @param  object  $object
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function akeeba_object_get($object, $key, $default = null)
	{
		if (is_null($key) or trim($key) == '') return $object;

		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_object($object) || ! isset($object->{$segment}))
			{
				return akeeba_value($default);
			}

			$object = $object->{$segment};
		}

		return $object;
	}
}

if ( ! function_exists('akeeba_preg_replace_sub'))
{
	/**
	 * Replace a given pattern with each value in the array in sequentially.
	 *
	 * @param  string  $pattern
	 * @param  array   $replacements
	 * @param  string  $subject
	 * @return string
	 */
	function akeeba_preg_replace_sub($pattern, &$replacements, $subject)
	{
		return preg_replace_callback($pattern, function($match) use (&$replacements)
		{
			return array_shift($replacements);

		}, $subject);
	}
}

if ( ! function_exists('akeeba_starts_with'))
{
	/**
	 * Determine if a given string starts with a given substring.
	 *
	 * @param  string        $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	function akeeba_starts_with($haystack, $needles)
	{
		foreach ((array) $needles as $needle)
		{
			if ($needle != '' && strpos($haystack, $needle) === 0) return true;
		}

		return false;
	}
}

if ( ! function_exists('akeeba_value'))
{
	/**
	 * Return the default value of the given value.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	function akeeba_value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}

if ( ! function_exists('akeeba_with'))
{
	/**
	 * Return the given object. Useful for chaining.
	 *
	 * @param  mixed  $object
	 * @return mixed
	 */
	function akeeba_with($object)
	{
		return $object;
	}
}
