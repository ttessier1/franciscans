<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Alice\Exception;

use RuntimeException;

/**
 * This exception tells ALICE to stop reading lines from the log file. It is not rethrown. It's only meant to stop the
 * scanning early.
 */
class StopScanningEarly extends RuntimeException
{
}