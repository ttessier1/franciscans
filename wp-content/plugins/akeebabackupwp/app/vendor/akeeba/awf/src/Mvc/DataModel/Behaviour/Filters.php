<?php
/**
 * @package   awf
 * @copyright Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Awf\Mvc\DataModel\Behaviour;

use Awf\Database\Query;
use Awf\Event\Observer;
use Awf\Mvc\DataModel;
use Awf\Registry\Registry;

class Filters extends Observer
{
	/**
	 * This event runs after we have built the query used to fetch a record
	 * list in a model. It is used to apply automatic query filters.
	 *
	 * @param   DataModel  &$model  The model which calls this event
	 * @param   Query      &$query  The query we are manipulating
	 *
	 * @return  void
	 */
	public function onAfterBuildQuery(&$model, &$query)
	{
		$tableKey = $model->getIdFieldName();
		$db       = $model->getDbo();

		$fields     = $model->getTableFields();
		$blacklist  = $model->getBlacklistFilters();
		$filterZero = $model->getBehaviorParam('filterZero', null);
		$tableAlias = $model->getBehaviorParam('tableAlias', null);

		foreach ($fields as $fieldname => $fieldmeta)
		{
			if (in_array($fieldname, $blacklist))
			{
				continue;
			}

			$fieldInfo = (object) [
				'name'       => $fieldname,
				'type'       => $fieldmeta->Type,
				'filterZero' => $filterZero,
				'tableAlias' => $tableAlias,
			];

			$filterName  = ($fieldInfo->name == $tableKey) ? 'id' : $fieldInfo->name;
			$filterState = $model->getState($filterName, null);

			// Special primary key handling: if ignore request is set we'll also look for an 'id' state variable if a
			// state variable by the same name as the key doesn't exist. If ignore request is not set in the model we
			// do not filter by 'id' since this interferes with going from an edit page to a browse page (the list is
			// filtered by id without user controls to unset it).
			if ($fieldInfo->name == $tableKey)
			{
				$filterState = $model->getState($filterName, null);

				if (!$model->getIgnoreRequest())
				{
					continue;
				}

				if (empty($filterState))
				{
					$filterState = $model->getState('id', null);
				}
			}

			$field = DataModel\Filter\AbstractFilter::getField($fieldInfo, ['dbo' => $db]);

			if (!is_object($field) || !($field instanceof DataModel\Filter\AbstractFilter))
			{
				continue;
			}

			if ((is_array($filterState)
			     && (
				     array_key_exists('value', $filterState)
				     || array_key_exists('from', $filterState)
				     || array_key_exists('to', $filterState)
			     ))
			    || is_object($filterState))
			{
				$options = new Registry($filterState);
			}
			else
			{
				$options = new Registry();
				$options->set('value', $filterState);
			}

			$methods = $field->getSearchMethods();
			$method  = $options->get('method', $field->getDefaultSearchMethod());

			if (!in_array($method, $methods))
			{
				$method = 'exact';
			}

			switch ($method)
			{
				case 'between':
				case 'outside':
				case 'range':
					$sql = $field->$method($options->get('from', null), $options->get('to'));
					break;

				case 'interval':
				case 'modulo':
					$sql = $field->$method($options->get('value', null), $options->get('interval'));
					break;

				case 'search':
					$sql = $field->$method($options->get('value', null), $options->get('operator', '='));
					break;

				case 'exact':
				case 'partial':
				default:
					$sql = $field->$method($options->get('value', null));
					break;
			}

			if ($sql)
			{
				$query->where($sql);
			}
		}
	}
}
