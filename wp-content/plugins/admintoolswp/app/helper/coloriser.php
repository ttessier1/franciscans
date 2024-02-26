<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

defined('ADMINTOOLSINC') or die;

class Coloriser
{
	public static function colorise($file, $onlyLast = false)
	{
		$ret = '';

		$lines = @file($file);
		if (empty($lines))
		{
			return $ret;
		}

		array_shift($lines);

		foreach ($lines as $line)
		{
			$line = trim($line);
			
			if (empty($line)) 
			{
				continue;
			}
			
			$type = substr($line, 0, 1);
			
			switch ($type)
			{
				case '=':
					continue 2;
					break;

				case '+':
					$ret .= "\t" . '<li><span class="akeeba-label--green">Added</span> ' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '-':
					$ret .= "\t" . '<li><span class="akeeba-label--grey">Removed</span> ' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '~':
				case '^':
					$ret .= "\t" . '<li><span class="akeeba-label--grey">Changed</span> ' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '*':
					$ret .= "\t" . '<li><span class="akeeba-label--red">Security</span> ' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '!':
					$ret .= "\t" . '<li><span class="akeeba-label--orange">Important</span> ' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				case '#':
					$ret .= "\t" . '<li><span class="akeeba-label--teal">Fixed</span> ' . htmlentities(trim(substr($line, 2))) . "</li>\n";
					break;

				default:
					if (!empty($ret))
					{
						$ret .= "</ul>";
						if ($onlyLast) return $ret;
					}
					
					if (!$onlyLast)
					{
						$ret .= "<h4>$line</h4>\n";
					}
					
					$ret .= "<ul class=\"akeeba-changelog\">\n";
					
					break;
			}
		}

		return $ret;
	}
}
