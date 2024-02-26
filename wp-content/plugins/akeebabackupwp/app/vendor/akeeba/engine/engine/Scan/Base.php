<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3, or later
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program. If not, see
 * <https://www.gnu.org/licenses/>.
 */

namespace Akeeba\Engine\Scan;

defined('AKEEBAENGINE') || die();

abstract class Base
{
	/**
	 * Gets all the files of a given folder
	 *
	 * @param   string   $folder    The absolute path to the folder to scan for files
	 * @param   integer  $position  The position in the file list to seek to. Use null for the start of list.
	 *
	 * @return  array  A simple array of files
	 */
	abstract public function getFiles($folder, &$position);

	/**
	 * Gets all the folders (subdirectories) of a given folder
	 *
	 * @param   string   $folder    The absolute path to the folder to scan for files
	 * @param   integer  $position  The position in the file list to seek to. Use null for the start of list.
	 *
	 * @return  array  A simple array of folders
	 */
	abstract public function getFolders($folder, &$position);
}
