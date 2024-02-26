<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Library\Mvc\Model;

use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Input\Input;

defined('ADMINTOOLSINC') or die;

abstract class Model
{
	/** @var Input  */
	protected $input;

	/** @var \Akeeba\AdminTools\Library\Database\Driver */
	protected $db;

	/** @var  string    Name of database table */
	protected $table;

	/** @var  string    Name of the primary key field */
	protected $pk;

	/**
	 * @param   Input   $input
	 */
	public function __construct($input)
	{
		$this->input = $input;
		$this->db    = Wordpress::getDb();
	}

	public function getTableName()
	{
		return $this->table;
	}

	public function getItems($overrideLimits = false, $limitstart = 0, $limit = 0)
	{
		if (!$overrideLimits)
		{
			$limitstart = $this->input->getInt('limitstart', 0);
			$limit      = $this->input->getInt('limit', Wordpress::get_page_limit());
		}

		$query = $this->buildQuery($overrideLimits);

		if (is_null($query))
		{
			return array();
		}

		$db = $this->db;

		$rows = $db->setQuery($query, $limitstart, $limit)->loadObjectList();

		if (method_exists($this, 'onAfterGetItems'))
		{
			$this->onAfterGetItems($rows);
		}

		return $rows;
	}

	/**
	 * Fetches a single record from the database given an ID
	 *
	 * @param array|mixed  $key  An optional primary key value to load the row by, or an array of fields to match.
	 *                           If not set the identity column's value is used
	 *
	 * @return bool|\stdClass
	 */
	public function getItem($key)
	{
		if (!$this->pk || !$this->table)
		{
			return false;
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn($this->table));

		// If it's not an array, assembled it based on the primary key
		if (!is_array($key))
		{
			$key = [$this->pk => $key];
		}

		// Then build the where clause
		foreach ($key as $field => $value)
		{
			$query->where($db->qn($field) . ' = ' . $db->q($value));
		}

		$item = $db->setQuery($query)->loadObject();

		return $item;
	}

	public function getTotal()
	{
		$db    = $this->getDbo();
		$query = $this->buildQuery(true);

		if (is_null($query))
		{
			return 0;
		}

		$query->clear('select')->select('COUNT(*)');

		$total = $db->setQuery($query)->loadResult();

		return $total;
	}

	/**
	 * Builds the query for retrieving the data
	 *
	 * @param bool $overrideLimits
	 *
	 * @return null|\Akeeba\AdminTools\Library\Database\Query
	 */
	public function buildQuery($overrideLimits = false)
	{
		return null;
	}

	public function getDbo()
	{
		return $this->db;
	}

	/**
	 * Saves current data. Data-aware models should implement the logic for this method
	 *
	 * @param   array   $data   Data that should be saved
	 *
	 * @return  int     The ID of the saved record
	 */
	public function save(array $data = array())
	{
		return 0;
	}

	/**
	 * Deletes one or more records
	 * TODO Move fetching the IDS into the controller
	 *
	 * @param null|array	$ids	List of ID that should be deleted
	 *
	 * @return bool
	 */
	public function delete($ids = null)
	{
		if (!$this->pk || !$this->table)
		{
			return false;
		}

		$db  = $this->db;

		$ids = (array) $ids;

		if (!$ids)
		{
			$ids = $this->input->get('cid', array(), 'raw');
		}

		$cleaned = array();

		foreach ($ids as $id)
		{
			$cleaned[] = (int) $id;
		}

		$query = $db->getQuery(true)
					->delete($db->qn($this->table))
					->where($db->qn($this->pk).' IN('.implode(',', $cleaned).')');
		$db->setQuery($query)->execute();

		// Call the "onAfterDelete" on each record
		if (method_exists($this, 'onAfterDelete'))
		{
			foreach ($cleaned as $id)
			{
				$this->onAfterDelete($id);
			}
		}

		return true;
	}

	/**
	 * Copy one or more items from the request, applying some custom data to them
	 * TODO Move fetching the IDS into the controller
	 *
	 * @param   array   $data   Custom data that we want to edit before saving back the record
	 *
	 * @return  bool
	 */
	public function copy(array $data = array())
	{
		if (!$this->pk || !$this->table)
		{
			return false;
		}

		$pk  = $this->pk;
		$ids = $this->input->get('cid', array(), 'raw');

		if (!$ids)
		{
			return true;
		}

		$cleaned = array();

		foreach ($ids as $id)
		{
			$cleaned[] = (int) $id;
		}

		foreach ($cleaned as $id)
		{
			$item = $this->getItem($id);

			// Cast as array, since the save methods expects an array
			$item = (array) $item;

			// Null the primary key and apply the eventual changes passed
			$item[$pk] = null;
			$item = array_merge($item, $data);

			$this->save($item);
		}

		return true;
	}

	public function getInput()
	{
		return $this->input;
	}

	public function setInput(Input $input)
	{
		$this->input = $input;
	}
}
