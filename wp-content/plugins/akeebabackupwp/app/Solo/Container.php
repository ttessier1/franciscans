<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo;

use Awf\Database\Driver;

/**
 * Dependency injection container for Solo
 *
 * @property-read  string  $iconBaseName  The base name for logo icon files
 */
class Container extends \Awf\Container\Container
{
	public function __construct(array $values = array())
	{
		$this->iconBaseName = 'solo';

		if (!isset($values['application_name']))
		{
			$values['application_name'] = 'Solo';
		}

		if (!isset($values['session_segment_name']))
		{
			$installationId = 'default';

			if (function_exists('base64_encode'))
			{
				$installationId = base64_encode(__DIR__);
			}

			if (function_exists('md5'))
			{
				$installationId = md5(__DIR__);
			}

			if (function_exists('sha1'))
			{
				$installationId = sha1(__DIR__);
			}

			$values['session_segment_name'] = $values['application_name'] . '_' . $installationId;
		}

		parent::__construct($values);
	}
}
