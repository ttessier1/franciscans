<?php
/**
 * @package   awf
 * @copyright Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Awf\Pagination;

use Awf\Application\Application;
use Awf\Container\Container;
use Awf\Container\ContainerAwareInterface;
use Awf\Container\ContainerAwareTrait;
use Awf\Exception\App;
use Awf\Input\Input;
use Awf\Text\Text;
use Awf\Uri\Uri;

class Pagination implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * @var    integer  The record number to start displaying from.
	 */
	public $limitStart = null;

	/**
	 * @var   integer  Number of rows to display per page.
	 */
	public $limit = null;

	/**
	 * @var   integer  Total number of rows.
	 */
	public $total = null;

	/**
	 * @var   integer  Value pagination object begins at
	 */
	public $pagesStart;

	/**
	 * @var   integer  Value pagination object ends at
	 */
	public $pagesStop;

	/**
	 * @var   integer  Current page
	 */
	public $pagesCurrent;

	/**
	 * @var   integer  Total number of pages
	 */
	public $pagesTotal;

	/**
	 * @var   integer  How many pages to display
	 */
	public $pagesDisplayed = 10;

	/**
	 * @var   boolean  View all flag
	 */
	protected $viewAll = false;

	/**
	 * Additional URL parameters to be added to the pagination URLs generated by the class.  These
	 * may be useful for filters and extra values when dealing with lists and GET requests.
	 *
	 * @var    array
	 */
	protected $additionalUrlParams = [];

	protected $application = null;

	/** @var  object    Pagination data object */
	protected $data = null;

	/**
	 * Constructor.
	 *
	 * Note: legacy application may provide an application as the last argument. This has been deprecated
	 *
	 * @param   int|null                    $total       The total number of items.
	 * @param   int|null                    $limitStart  The offset of the item to start at.
	 * @param   int|null                    $limit       The number of items to display per page.
	 * @param   int|null                    $displayed   Maximum number of page links to display (default: 10)
	 * @param   Container|Application|null  $app         The container object
	 *
	 * @throws App
	 */
	public function __construct(?int $total, ?int $limitStart, ?int $limit, ?int $displayed = 10, $container = null)
	{
		/** @deprecated 2.0 You will have to provide the container in the constructor */
		if (empty($container))
		{
			trigger_error(
				sprintf('The container argument is mandatory in %s', __METHOD__),
				E_USER_DEPRECATED
			);

			$container = Application::getInstance()->getContainer();
		}

		if ($container instanceof Application)
		{
			trigger_error(
				sprintf('The last argument to %s must be a Container, not an application', __METHOD__),
				E_USER_DEPRECATED
			);

			$container = $container->getContainer();
		}

		$this->setContainer($container);

		// Value/type checking.
		$this->total      = (int) ($total ?? 0);
		$this->limitStart = (int) max($limitStart ?? 0, 0);
		$this->limit      = (int) max($limit ?? 0, 0);

		if ($this->limit > $this->total)
		{
			$this->limitStart = 0;
		}

		if (!$this->limit)
		{
			$this->limit      = $total;
			$this->limitStart = 0;
		}

		/*
		 * If limitStart is greater than total (i.e. we are asked to display records that don't exist)
		 * then set limitStart to display the last natural page of results
		 */
		if ($this->limitStart > $this->total - $this->limit)
		{
			$this->limitStart = max(0, (int) (ceil($this->total / $this->limit) - 1) * $this->limit);
		}

		// Set the total pages and current page values.
		if ($this->limit > 0)
		{
			$this->pagesTotal   = ceil($this->total / $this->limit);
			$this->pagesCurrent = ceil(($this->limitStart + 1) / $this->limit);
		}

		// Set the pagination iteration loop values.
		$this->pagesDisplayed = $displayed;
		$displayedPages       = $this->pagesDisplayed;
		$this->pagesStart     = $this->pagesCurrent - ($displayedPages / 2);

		if ($this->pagesStart < 1)
		{
			$this->pagesStart = 1;
		}

		if ($this->pagesStart + $displayedPages > $this->pagesTotal)
		{
			$this->pagesStop = $this->pagesTotal;

			if ($this->pagesTotal < $displayedPages)
			{
				$this->pagesStart = 1;
			}
			else
			{
				$this->pagesStart = $this->pagesTotal - $displayedPages + 1;
			}
		}
		else
		{
			$this->pagesStop = $this->pagesStart + $displayedPages - 1;
		}

		// If we are viewing all records set the view all flag to true.
		if ($limit == 0)
		{
			$this->viewAll = true;
		}

		// Automatically set the URL parameters
		$this->setAdditionalUrlParamsFromInput();
	}

	/**
	 * Method to set an additional URL parameter to be added to all pagination class generated
	 * links. When $value is null, $key is removed from the list of additional URL parameters.
	 *
	 * @param   string  $key    The name of the URL parameter for which to set a value.
	 * @param   mixed   $value  The value to set for the URL parameter.
	 *
	 * @return  mixed  The old value for the parameter.
	 */
	public function setAdditionalUrlParam($key, $value)
	{
		// Never add the limit parameters; that would break things badly!
		if (in_array($key, ['limit', 'limitstart']))
		{
			return false;
		}

		// Get the old value to return and set the new one for the URL parameter.
		$result = $this->additionalUrlParams[$key] ?? null;

		// If the passed parameter value is null unset the parameter, otherwise set it to the given value.
		if ($value === null)
		{
			unset($this->additionalUrlParams[$key]);
		}
		else
		{
			$this->additionalUrlParams[$key] = $value;
		}

		return $result;
	}

	/**
	 * Sets the additional URL parameters from the input. If no input is specified we will use the application's
	 * input. The URL parameters of the base URL will be automatically removed.
	 *
	 * @param   Input  $input  The input object to use
	 *
	 * @return  void
	 */
	public function setAdditionalUrlParamsFromInput(Input $input = null)
	{
		// Make sure we have an input
		if (!is_object($input))
		{
			$input = $this->getContainer()->input;
		}

		// Get the input's data array
		$data = $input->getData();

		// Get the rebase URL parameters to eliminate
		$rebase          = Uri::rebase('index.php', $this->getContainer());
		$rebase          = new Uri($rebase);
		$eliminateParams = $rebase->getQuery(true);
		$eliminateParams = array_keys($eliminateParams);

		// Set the additional URL parameters
		foreach ($data as $key => $value)
		{
			// We can't process object data automatically
			if (is_object($value))
			{
				continue;
			}

			// We can't process array data automatically
			if (is_array($value))
			{
				continue;
			}

			// Ignore the URL parameters from the URL rebasing
			if (in_array($key, $eliminateParams))
			{
				continue;
			}

			$this->setAdditionalUrlParam($key, $value);
		}
	}

	/**
	 * Clears the additional URL parameters
	 *
	 * @return  void
	 */
	public function clearAdditionalUrlParams()
	{
		$this->additionalUrlParams = [];
	}

	/**
	 * Method to get an additional URL parameter (if it exists) to be added to
	 * all pagination class generated links.
	 *
	 * @param   string  $key  The name of the URL parameter for which to get the value.
	 *
	 * @return  mixed  The value if it exists or null if it does not.
	 */
	public function getAdditionalUrlParam($key)
	{
		return $this->additionalUrlParams[$key] ?? null;
	}

	/**
	 * Return all of the additional URL parameters and their values in a key/value array
	 *
	 * @return  array
	 */
	public function getAdditionalUrlParams()
	{
		return $this->additionalUrlParams;
	}

	/**
	 * Set/unset multiple URL parameters at once.
	 *
	 * @param   array  $params  A key/value array of the additional URL parameters to set/unset
	 *
	 * @return  void
	 * @see     Pagination::setAdditionalUrlParam()
	 *
	 */
	public function setAdditionalUrlParams(array $params)
	{
		foreach ($params as $key => $value)
		{
			$this->setAdditionalUrlParam($key, $value);
		}
	}

	/**
	 * Return the normalised offset for a row with a given index.
	 *
	 * @param   integer  $index  The row index
	 *
	 * @return  integer  Normalised offset for a row with a given index.
	 */
	public function getRowOffset($index)
	{
		return $index + 1 + $this->limitStart;
	}

	/**
	 * Return the pagination data object, only creating it if it doesn't already exist.
	 *
	 * @return  object   Pagination data object.
	 */
	public function getData()
	{
		$this->data = $this->data ?? $this->_buildDataObject();

		return $this->data;
	}

	/**
	 * Create and return the pagination pages counter string, ie. Page 2 of 4.
	 *
	 * @return  string   Pagination pages counter string.
	 */
	public function getPagesCounter()
	{
		$html = null;

		if ($this->pagesTotal > 1)
		{
			$html .= $this->getContainer()->language->sprintf('AWF_PAGINATION_LBL_PAGE_CURRENT_OF_TOTAL', $this->pagesCurrent, $this->pagesTotal);
		}

		return $html;
	}

	/**
	 * Create and return the pagination result set counter string, e.g. Results 1-10 of 42
	 *
	 * @return  string   Pagination result set counter string.
	 */
	public function getResultsCounter()
	{
		$html       = null;
		$fromResult = $this->limitStart + 1;

		// If the limit is reached before the end of the list.
		if ($this->limitStart + $this->limit < $this->total)
		{
			$toResult = $this->limitStart + $this->limit;
		}
		else
		{
			$toResult = $this->total;
		}

		// If there are results found.
		if ($this->total > 0)
		{
			$msg  = $this->getContainer()->language->sprintf('AWF_PAGINATION_LBL_RESULTS_OF', $fromResult, $toResult, $this->total);
			$html .= "\n" . $msg;
		}
		else
		{
			$html .= "\n" . $this->getContainer()->language->text('AWF_PAGINATION_LBL_NO_RESULTS');
		}

		return $html;
	}

	/**
	 * Create and return the pagination page list string, ie. Previous, Next, 1 2 3 ... x.
	 *
	 * @return  string  Pagination page list string.
	 */
	public function getPagesLinks()
	{
		// Build the page navigation list.
		$data = $this->_buildDataObject();

		$list = [];

		$itemOverride = false;
		$listOverride = false;

		$templatePath = $this->getContainer()->templatePath;
		$chromePath   = $templatePath . '/' . $this->getContainer()->application->getTemplate() . '/php/pagination.php';

		if (file_exists($chromePath))
		{
			include_once $chromePath;

			if (function_exists('_akeeba_pagination_item_active')
			    && function_exists(
				    '_akeeba_pagination_item_inactive'
			    ))
			{
				$itemOverride = true;
			}

			if (function_exists('_akeeba_pagination_list_render'))
			{
				$listOverride = true;
			}
		}

		// Build the select list
		$list['all']['current'] = false;
		if ($data->all->base !== null)
		{
			$list['all']['active'] = true;
			$list['all']['data']   = ($itemOverride)
				? _akeeba_pagination_item_active($data->all)
				: $this->_item_active(
					$data->all
				);
		}
		else
		{
			$list['all']['active'] = false;
			$list['all']['data']   = ($itemOverride) ? _akeeba_pagination_item_inactive($data->all)
				: $this->_item_inactive($data->all);
		}

		$list['start']['current'] = false;
		if ($data->start->base !== null)
		{
			$list['start']['active'] = true;
			$list['start']['data']   = ($itemOverride) ? _akeeba_pagination_item_active($data->start)
				: $this->_item_active($data->start);
		}
		else
		{
			$list['start']['active'] = false;
			$list['start']['data']   = ($itemOverride) ? _akeeba_pagination_item_inactive($data->start)
				: $this->_item_inactive($data->start);
		}

		$list['previous']['current'] = false;
		if ($data->previous->base !== null)
		{
			$list['previous']['active'] = true;
			$list['previous']['data']   = ($itemOverride) ? _akeeba_pagination_item_active($data->previous)
				: $this->_item_active($data->previous);
		}
		else
		{
			$list['previous']['active'] = false;
			$list['previous']['data']   = ($itemOverride) ? _akeeba_pagination_item_inactive($data->previous)
				: $this->_item_inactive($data->previous);
		}

		// Make sure it exists
		$list['pages'] = [];

		foreach ($data->pages as $i => $page)
		{
			$list['pages'][$i]['current'] = $this->pagesCurrent == $i;
			$list['pages'][$i]['active']  = true;
			$list['pages'][$i]['data']    = ($itemOverride) ? _akeeba_pagination_item_active($page)
				: $this->_item_active($page);
		}

		$list['next']['current'] = false;
		if ($data->next->base !== null)
		{
			$list['next']['active'] = true;
			$list['next']['data']   = ($itemOverride) ? _akeeba_pagination_item_active($data->next)
				: $this->_item_active($data->next);
		}
		else
		{
			$list['next']['active'] = false;
			$list['next']['data']   = ($itemOverride) ? _akeeba_pagination_item_inactive($data->next)
				: $this->_item_inactive($data->next);
		}

		$list['end']['current'] = false;
		if ($data->end->base !== null)
		{
			$list['end']['active'] = true;
			$list['end']['data']   = ($itemOverride)
				? _akeeba_pagination_item_active($data->end)
				: $this->_item_active(
					$data->end
				);
		}
		else
		{
			$list['end']['active'] = false;
			$list['end']['data']   = ($itemOverride) ? _akeeba_pagination_item_inactive($data->end)
				: $this->_item_inactive($data->end);
		}

		if ($this->total > $this->limit)
		{
			return ($listOverride) ? _akeeba_pagination_list_render($list, $this) : $this->_list_render($list);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Return the pagination footer.
	 *
	 * @return  string  Pagination footer.
	 */
	public function getListFooter(?array $limitBoxAttributes = null)
	{
		$list                 = [];
		$list['limit']        = $this->limit;
		$list['limitstart']   = $this->limitStart;
		$list['total']        = $this->total;
		$list['limitfield']   = $this->getLimitBox($limitBoxAttributes);
		$list['pagescounter'] = $this->getPagesCounter();
		$list['pageslinks']   = $this->getPagesLinks();

		$templatePath = $this->getContainer()->templatePath;
		$chromePath   = $templatePath . '/' . $this->getContainer()->application->getTemplate() . '/php/pagination.php';

		if (file_exists($chromePath))
		{
			include_once $chromePath;

			if (function_exists('_akeeba_pagination_list_footer'))
			{
				return _akeeba_pagination_list_footer($list);
			}
		}

		return $this->_list_footer($list);
	}

	/**
	 * Creates a dropdown box for selecting how many records to show per page.
	 *
	 * @param   array|null  $attributes  The attributes of the limit box, null to use the default
	 *
	 * @return  string  The HTML for the limit # input box.
	 */
	public function getLimitBox($attributes = null)
	{
		$container = $this->getContainer();
		$limits    = [];

		// Make the option list.
		for ($i = 5; $i <= 30; $i += 5)
		{
			$limits[] = $container->html->get('select.option', $i);
		}

		$limits[] = $container->html->get('select.option', '50', $this->getContainer()->language->text('AWF_50'));
		$limits[] = $container->html->get('select.option', '100', $this->getContainer()->language->text('AWF_100'));
		$limits[] = $container->html->get('select.option', '0', $this->getContainer()->language->text('AWF_ALL'));

		$selected = $this->viewAll ? 0 : $this->limit;

		// Use default attributes if none is specified
		if (is_null($attributes))
		{
			$attributes = [
				'class'    => 'input-sm',
				'size'     => 1,
				'onchange' => 'this.form.submit()',
			];
		}

		// Build the select list.
		$html = $container->html->get(
			'select.genericList',
			$limits,
			'limit',
			$attributes,
			'value',
			'text',
			$selected
		);

		return $html;
	}

	/**
	 * Create the HTML for a list footer
	 *
	 * @param   array  $list  Pagination list data structure.
	 *
	 * @return  string  HTML for a list footer
	 */
	protected function _list_footer($list)
	{
		$html = "<div class=\"list-footer\">\n";

		$html .= "\n<div class=\"limit\">" . $this->getContainer()->language->text('AWF_COMMON_LBL_DISPLAY_NUM') . $list['limitfield'] . "</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"counter\">" . $list['pagescounter'] . "</div>";

		$html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"" . $list['limitstart'] . "\" />";
		$html .= "\n</div>";

		return $html;
	}

	/**
	 * Create the html for a list footer
	 *
	 * @param   array  $list  Pagination list data structure.
	 *
	 * @return  string  HTML for a list start, previous, next,end
	 */
	protected function _list_render($list)
	{
		// Reverse output rendering for right-to-left display.
		$html = '<ul class="pagination">';

		if ($this->pagesStart > 1)
		{
			$class = $list['start']['active'] ? '' : ' class="disabled"';
			$html  .= '<li' . $class . '>' . $list['start']['data'] . '</li>';
		}

		$class = $list['previous']['active'] ? '' : ' class="disabled"';
		$html  .= '<li' . $class . '>' . $list['previous']['data'] . '</li>';

		foreach ($list['pages'] as $page)
		{
			$class = $page['active'] ? ($page['current'] ? 'active' : '') : 'disabled';
			$class = empty($class) ? '' : ' class="' . $class . '"';
			$html  .= '<li' . $class . '>' . $page['data'] . '</li>';
		}

		$class = $list['next']['active'] ? '' : ' class="disabled"';
		$html  .= '<li' . $class . '>' . $list['next']['data'] . '</li>';

		if ($this->pagesStop < $this->pagesTotal)
		{
			$class = $list['end']['active'] ? '' : ' class="disabled"';
			$html  .= '<li' . $class . '>' . $list['end']['data'] . '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Method to create an active pagination link to the item
	 *
	 * @param   \Awf\Pagination\PaginationObject  $item  The object with which to make an active link.
	 *
	 * @return  string  HTML link
	 */

	protected function _item_active(\Awf\Pagination\PaginationObject $item)
	{
		return '<a href="' . $item->link . '">' . $item->text . '</a>';
	}

	/**
	 * Method to create an inactive pagination string
	 *
	 * @param   \Awf\Pagination\PaginationObject  $item  The item to be processed
	 *
	 * @return  string
	 */
	protected function _item_inactive(\Awf\Pagination\PaginationObject $item)
	{
		return '<span>' . $item->text . '</span>';
	}

	/**
	 * Create and return the pagination data object.
	 *
	 * @return  object  Pagination data object.
	 */
	protected function _buildDataObject()
	{
		$data   = new \stdClass;
		$router = $this->getContainer()->router;

		// Build the additional URL parameters string.
		$params = '';

		if (!empty($this->additionalUrlParams))
		{
			foreach ($this->additionalUrlParams as $key => $value)
			{
				$params .= '&' . $key . '=' . $value;
			}
		}

		$params = 'index.php?' . substr($params, 1);

		$data->all = new PaginationObject($this->getContainer()->language->text('AWF_PAGINATION_LBL_VIEW_ALL'));

		if (!$this->viewAll)
		{
			$data->all->base = '0';
			$data->all->link = $router->route($params . '&limitstart=');
		}

		// Set the start and previous data objects.
		$data->start    = new PaginationObject('&laquo;');
		$data->previous = new PaginationObject('&lsaquo;');

		if ($this->pagesCurrent > 1)
		{
			$page = ($this->pagesCurrent - 2) * $this->limit;

			$data->start->base = '0';
			$data->start->link = $router->route($params . '&limitstart=0');

			$data->previous->base = $page;
			$data->previous->link = $router->route($params . '&limitstart=' . $page);
		}

		// Set the next and end data objects.
		$data->next = new PaginationObject('&rsaquo;');
		$data->end  = new PaginationObject('&raquo;');

		if ($this->pagesCurrent < $this->pagesTotal)
		{
			$next = $this->pagesCurrent * $this->limit;
			$end  = ($this->pagesTotal - 1) * $this->limit;

			$data->next->base = $next;
			$data->next->link = $router->route($params . '&limitstart=' . $next);

			$data->end->base = $end;
			$data->end->link = $router->route($params . '&limitstart=' . $end);
		}

		$data->pages = [];
		$stop        = $this->pagesStop;

		for ($i = $this->pagesStart; $i <= $stop; $i++)
		{
			$offset = ($i - 1) * $this->limit;

			$data->pages[$i] = new PaginationObject($i);

			if ($i != $this->pagesCurrent || $this->viewAll)
			{
				$data->pages[$i]->base = $offset;
				$data->pages[$i]->link = $router->route($params . '&limitstart=' . $offset);
			}
			else
			{
				$data->pages[$i]->active = true;
			}
		}

		return $data;
	}
}
