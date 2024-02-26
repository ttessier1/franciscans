<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class BlacklistedAddresses extends Model
{
	public function __construct(Input $input)
	{
		parent::__construct($input);

		$this->table = '#__admintools_ipblock';
		$this->pk    = 'id';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn($this->table));

		$fltIP = $this->input->getString('ip', null);

		if ($fltIP)
		{
			$fltIP = '%' . $fltIP . '%';
			$query->where($db->qn('ip') . ' LIKE ' . $db->q($fltIP));
		}

		$fltDescr = $this->input->getString('description', null);

		if ($fltDescr)
		{
			$fltDescr = '%' . $fltDescr . '%';
			$query->where($db->qn('description') . ' LIKE ' . $db->q($fltDescr));
		}

		if (!$overrideLimits)
		{
			$ordering  = $this->input->getCmd('ordering', '');
			$direction = $this->input->getCmd('order_dir', '');

			if (!in_array($ordering, ['id', 'ip']))
			{
				$ordering = 'id';
			}

			if (!in_array($direction, ['asc', 'desc']))
			{
				$direction = 'desc';
			}

			$query->order($db->qn($ordering) . ' ' . $direction);
		}

		return $query;
	}

	/**
	 * Decodes a single value (1,2,3) to an array containing the field delimiter and enclosure
	 *
	 * @param   int  $delimiter
	 *
	 * @return  array   [0] => field delimiter, [1] => enclosure char
	 */
	public function decodeDelimiterOptions($delimiter)
	{
		switch ($delimiter)
		{
			case 1:
				return [',', ''];
				break;

			case 2:
				return [';', ''];
				break;

			default:
				return [';', '"'];
				break;
		}
	}

	/**
	 * Parses a CSV file, importing every row
	 *
	 * @param   string  $file            Uploaded file
	 * @param   string  $fieldDelimiter  Fields separator, such as ";" or ","
	 * @param   string  $fieldQuotes     Field quotes such as " or '
	 *
	 * @return  int  The number of imported users.
	 */
	public function import($file, $fieldDelimiter, $fieldQuotes)
	{
		$result = 0;
		$i      = 0;
		$errors = [];

		if (!$file)
		{
			throw new \RuntimeException(Language::_('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_FILE'));
		}

		$handle = fopen($file, 'r');

		while (true)
		{
			// Read the next line
			$line = '';

			while (!feof($handle) && (strpos($line, "\n") === false) && (strpos($line, "\r") === false))
			{
				$line .= fgets($handle, 65536);
			}

			// Past EOF and no data read? Break.
			if (empty($line) && feof($handle))
			{
				break;
			}

			// Did we read more than one line?
			if (!in_array(substr($line, -1), ["\r", "\n"]))
			{
				// Get the position of linefeed and carriage return characters in the line read
				$posLF = strpos($line, "\n");
				$posCR = strpos($line, "\r");

				// Determine line ending
				if (($posCR !== false) && ($posLF !== false))
				{
					// We have both \r and \n. Are they strung together?
					if ($posLF - $posCR == 1)
					{
						// Yes. Windows/DOS line termination.
						$searchCharacter = "\r\n";
					}
					else
					{
						// Nope. It's either Mac OS Classic or UNIX. Which one?
						if ($posCR < $posLF)
						{
							// Mac OS Classic
							$searchCharacter = "\r";
						}
						else
						{
							// UNIX
							$searchCharacter = "\n";
						}
					}
				}
				elseif ($posCR !== false)
				{
					$searchCharacter = "\r";
				}
				elseif ($posLF !== false)
				{
					$searchCharacter = "\n";
				}
				else
				{
					$searchCharacter = null;
				}

				// Roll back the file
				if (!is_null($searchCharacter))
				{
					$pos      = strpos($line, $searchCharacter);
					$rollback = strlen($line) - strpos($line, $searchCharacter);
					fseek($handle, -$rollback + strlen($searchCharacter), SEEK_CUR);
					// And chop the line
					$line = substr($line, 0, $pos);
				}
			}

			// Handle DOS and Mac OS classic line breaks
			$line = str_replace("\r\n", "\n", $line);
			$line = str_replace("\r", "\n", $line);
			$line = trim($line);

			if (empty($line))
			{
				continue;
			}

			// I have to use this weird structure because if an user passes an empty char as field enclosure
			// str_getcsv will return false, so I have to omit it, forcing PHP to use the function default one
			if ($fieldQuotes)
			{
				$data = str_getcsv($line, $fieldDelimiter, $fieldQuotes);
			}
			else
			{
				$data = str_getcsv($line, $fieldDelimiter);
			}

			if ($data === false)
			{
				break;
			}

			$i++;

			// Skip first line, there are headers in the file, so let's map them and then continue
			if ($i == 1)
			{
				continue;
			}

			if (!count($data))
			{
				$errors[] = Language::sprintf('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_LINE', $i);

				continue;
			}

			if (!isset($data[1]))
			{
				$data[1] = 'Imported IP on ' . date('Y-m-d');
			}

			$this->importRows($data);

			$result++;
		}

		fclose($handle);

		$this->importRows();

		// Did I had any errors?
		if ($errors)
		{
			throw new \RuntimeException(implode("<br/>", $errors));
		}

		return $result;
	}

	public function save(array $data = [])
	{
		$db = $this->getDbo();

		if (!$data)
		{
			$data = [
				'id'          => $this->input->getInt('id', 0),
				'ip'          => $this->input->getString('ip', ''),
				'description' => $this->input->getString('description', ''),
			];
		}

		if (!isset($data['id']))
		{
			$data['id'] = '';
		}

		$data = (object) $data;

		if (!$data->ip)
		{
			throw new \RuntimeException(Language::_('COM_ADMINTOOLS_ERR_BLACKLISTEDADDRESS_NEEDS_IP'));
		}

		if (!$data->id)
		{
			$db->insertObject($this->table, $data, 'id');
		}
		else
		{
			$db->updateObject($this->table, $data, ['id']);
		}

		return $data->id;
	}

	protected function importRows($data = null)
	{
		static $cache = [];

		// Let's enqueue the data
		if ($data)
		{
			$cache[] = $data;
		}

		// Did we grow over the limit or are forced to flush it? If so let's build the actual query
		// and execute it
		if (count($cache) >= 100 || !$data)
		{
			$db = $this->getDbo();

			$query = $db->getQuery(true)
				->insert($db->qn('#__admintools_ipblock'))
				->columns([$db->qn('ip'), $db->qn('description')]);

			foreach ($cache as $row)
			{
				$query->values($db->q($row[0]) . ', ' . $db->q($row[1]));
			}

			$db->setQuery($query)->execute();

			$cache = [];
		}
	}
}
