<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Uri\Uri;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureUploadshield extends AtsystemFeatureAbstract
{
	protected $loadOrder = 370;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if ($this->skipFiltering)
		{
			return false;
		}

		return ($this->cparams->getValue('uploadshield', 1) == 1);
	}

	/**
	 * Scans all uploaded files for PHP tags. This prevents uploading PHP files or crafted
	 * images with raw PHP code in them which may lead to arbitrary code execution under
	 * several common circumstances. It will also block files with null bytes in their
	 * filenames or with double extensions which include PHP in them (e.g. .php.jpg).
	 */
	public function onSystem()
	{
		// Do we have uploaded files?
		$input     = $this->input->files;
		$filesHash = $input->getData();

		if (!$this->shouldRun())
		{
			return;
		}

		if (empty($filesHash))
		{
			return;
		}

		foreach ($filesHash as $key => $temp_descriptor)
		{
			if (is_array($temp_descriptor) && !array_key_exists('tmp_name', $temp_descriptor))
			{
				$descriptors = $temp_descriptor;
			}
			else
			{
				$descriptors[] = $temp_descriptor;
			}

			unset($temp_descriptor);

			foreach ($descriptors as $descriptor)
			{
				$files = array();

				if (is_array($descriptor['tmp_name']))
				{
					foreach ($descriptor['tmp_name'] as $key => $value)
					{
						$files[] = array(
							'name'     => $descriptor['name'][$key],
							'type'     => $descriptor['type'][$key],
							'tmp_name' => $descriptor['tmp_name'][$key],
							'error'    => $descriptor['error'][$key],
							'size'     => $descriptor['size'][$key],
						);
					}
				}
				else
				{
					$files[] = $descriptor;
				}

				foreach ($files as $fileDescriptor)
				{
					$tempNames = $fileDescriptor['tmp_name'];
					$intendedNames = $fileDescriptor['name'];

					if (!is_array($tempNames))
					{
						$tempNames = array($tempNames);
					}

					if (!is_array($intendedNames))
					{
						$intendedNames = array($intendedNames);
					}

					$len = count($tempNames);

					for ($i = 0; $i < $len; $i++)
					{
						$tempName = array_shift($tempNames);
						$intendedName = array_shift($intendedNames);

						$extraInfo = "File descriptor :\n";
						$extraInfo .= print_r($fileDescriptor, true);
						$extraInfo .= "\n";

						// Empty file name (ie the field has been submitted, but it has no actual value in here), simply skip
						if (!$tempName)
						{
							continue;
						}

						// 1. Null byte check
						if (strstr($intendedName, "\u0000"))
						{
							$extraInfo .= "Block reason: null byte\n";

							$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 2. PHP-in-extension check
						$explodedName = explode('.', $intendedName);
						$explodedName = array_reverse($explodedName);

						// 2a. File extension is .php
						if ((count($explodedName) > 1) && (strtolower($explodedName[0]) == 'php'))
						{
							$extraInfo .= "Block reason: file extension is .php\n";

							$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 2a. File extension is php.xxx
						if ((count($explodedName) > 2) && (strtolower($explodedName[1]) == 'php'))
						{
							$extraInfo .= "Block reason: file extension is in the form of .php.xxx\n";

							$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// 2b. File extensions is php.xxx.yyy
						if ((count($explodedName) > 3) && (strtolower($explodedName[2]) == 'php'))
						{
							$extraInfo .= "Block reason: file extension is in the form of .php.xxx.yyy\n";

							$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

							return;
						}

						// For whatever reason we can't access file contents, skip it
						if (!@file_exists($tempName) || !@is_readable($tempName))
						{
							continue;
						}

						// 3. Contents scanner
						$fp = @fopen($tempName, 'r');

						if ($fp === false)
						{
							continue;
						}

						// Initialise
						$data = '';
						$extension = strtolower($explodedName[0]);
						$possibleFileForShortTagSyntax = in_array($extension, array(
							'inc', 'phps', 'class', 'php3', 'php4', 'txt', 'dat',  'tpl', 'tmpl'
						));

						// Process the file in 128Kb chunks
						while (!feof($fp))
						{
							// Read 128Kb and add it to the existing data (the last 4 bytes of the previous scan)
							$buffer = @fread($fp, 131072);
							$data .= $buffer;

							// Do we have a regular PHP tag?
							if (stristr($buffer, '<?php'))
							{
								$extraInfo .= "Block reason: file contains PHP open tag\n";

								$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

								return;
							}

							// Do we have a (possibly concealed) PHAR file?
							if (strstr($buffer, '__HALT_COMPILER'))
							{
								$extraInfo .= "Block reason: file is a possibly concealed PHAR file\n";

								$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

								return;
							}

							// If we have text file which may have the short tag (<?) in it...
							if ($possibleFileForShortTagSyntax)
							{
								// ...do I have a short tag?
								if (strstr($buffer, '<?'))
								{
									$extraInfo .= "Block reason: file contains PHP short tag\n";

									$this->exceptionsHandler->blockRequest('uploadshield', null, $extraInfo);

									return;
								}
							}

							// Keep the last 4 bytes of data to make sure we can catch partial strings.
							$data = substr($data, -14);

							// WARNING: Do NOT try seek to an earlier position! Here's how it all works.
							//
							// We just need to keep the last four bytes in $data so we can append the next 128Kb.
							// This way if the start of the tag is in the previous block and the rest is in the next
							// 128Kb block we can still scan it. The value 14 is not random. __HALT_COMPILER is 15 characters
							// and the longest string we're trying to detect. If it existed in this block we'd have
							// already found it and blocked it. Therefore the only possibility is that this block
							// ended in any of the first 14 characters of that substring.
							//
							// Do NOT seek to an earlier file position. It would be a rather silly thing to do.
						}

						fclose($fp);
					}
				}
			}
		}
	}

	private function shouldRun()
	{
		// Triple check that the action is the correct one and in the correct request array
		// Are we trying to upload a plugin? If not, always run
		if (!isset($_GET['action']) || $_GET['action'] != 'upload-plugin')
		{
			return true;
		}

		// Request param is correct, but are we on the correct page?
		// If we're inside WordPress, let's use core functions to check it

		$uri = Uri::getInstance();

		if (function_exists('admin_url'))
		{
			$plugin_url = trim(admin_url(), '/') . '/update.php';

			// Current URL matches the admin_url + the plugin page, this is a trusted access
			if ($uri->toString(['scheme', 'host', 'path']) == $plugin_url)
			{
				return false;
			}
		}
		else
		{
			// Otherwise let's fall back to a partial match if we're in auto-prepend mode
			$plugin_page = 'wp-admin/update.php';

			// PHP 7 implementation of the "str_ends_with" function
			if (substr_compare($uri->getPath(), $plugin_page, -strlen($plugin_page)) == 0)
			{
				// Do not run if we're manually uploading a ZIP file containing a plugin, this is a legitimate use
				return false;
			}
		}

		return true;
	}
}
