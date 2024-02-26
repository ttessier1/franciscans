<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\Engine\Util\ParseIni;

define('AKEEBAENGINE', 1);
require_once __DIR__ . '/engine/Util/ParseIni.php';

class Converter
{
	private $iniPath = '';

	private $jsonPath = '';

	public function __construct($inipath)
	{
		$this->iniPath  = $inipath;
		$baseName       = basename($inipath, '.ini');
		$this->jsonPath = dirname($inipath) . '/' . $baseName . '.json';
	}

	public function convert()
	{
		$parsedData = ParseIni::parse_ini_file_php($this->iniPath, true);
		$jsonData   = json_encode($parsedData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);

		file_put_contents($this->jsonPath, $jsonData);
		unlink($this->iniPath);
	}
}

class Scanner
{
	private $root = '';

	public function __construct($root)
	{
		$this->root = $root;
	}

	public function scanAndConvert($root = null, $dive = true)
	{
		$di = new DirectoryIterator(is_null($root) ? $this->root : $root);

		foreach ($di as $entry)
		{
			if ($entry->isDot())
			{
				continue;
			}

			if ($entry->isLink())
			{
				continue;
			}

			if ($entry->isDir())
			{
				if ($dive)
				{
					$this->scanAndConvert($entry->getPathname());
				}

				continue;
			}

			if ($entry->getExtension() !== 'ini')
			{
				continue;
			}

			echo "{$entry->getPathname()}\n";

			(new Converter($entry->getPathname()))->convert();
		}
	}
}

$root = __DIR__ . '/engine';
$dive = true;

if ($argc >= 2)
{
	$root = $argv[1];
}

if (($argc >= 3) && ($argv[2] == '--shallow'))
{
	$dive = false;
}

(new Scanner($root))->scanAndConvert();