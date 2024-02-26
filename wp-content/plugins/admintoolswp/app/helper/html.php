<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Uri\Uri;

defined('ADMINTOOLSINC') or die;

abstract class Html
{
	/**
	 * Creates a select list for bulk actions. The name of the field is "task" and not "action"
	 * for consistency.
	 *
	 * @param array $actions
	 *
	 * @return string
	 */
	public static function bulkActions(array $actions)
	{
		$html  = '<div class="alignleft actions bulkactions">';
		$html .=    '<select name="task" id="bulk-action-selector-top">';
		$html .=        '<option value="-1">'.__( 'Bulk Actions' ).'</option>';

		foreach ($actions as $action)
		{
			if (is_array($action))
			{
				$value = $action['value'];
				$text  = Language::_('COM_ADMINTOOLS_LBL_COMMON_ACTION_'.strtoupper($action['text']));
			}
			else
			{
				$value = $action;
				$text  = Language::_('COM_ADMINTOOLS_LBL_COMMON_ACTION_'.strtoupper($action));
			}

			$html .=    '<option value="'.$value.'">'.$text.'</option>';
		}

		$html .=    '</select>';
		$html .=    '<input type="submit" id="doaction" class="akeeba-btn--primary" value="'.__('Apply').'">';
		$html .= '</div>';

		return $html;
	}

	public static function listItemTask($id, $task, $text)
	{
		$text = Language::_($text);
		$html = <<<HTML
<a class="akeeba-btn--small--dark" href="javascript:void(0)" onclick="return atwpListItemTask('$id', '$task')">
	$text	
</a> 
HTML;

		return $html;
	}

	/**
	 * Creates a table header following Wordpress look & feel
	 *
	 * @param   Input   $input          Global input object, required to know the current ordering
	 * @param   string  $label          Label to display
	 * @param   string  $column_name    Name of the column, used for ordering
	 * @param   string  $style          Inline style to apply to the column
	 *
	 * @return string
	 */
	public static function tableHeader($input, $label, $column_name, $style = '')
	{
		$url = Uri::getInstance();
		$url->setVar('paged', null);
		$url->setVar('task', null);

		$url->setVar('ordering', $column_name);
		$url->setVar('order_dir', 'desc');

		$ordering  = $input->getCmd('ordering', '');
		$order_dir = $input->getCmd('order_dir', '');

		// $ordering_class  = 'sortable';
		$order_dir_class = 'akion-android-arrow-dropup';

		// If we are handling a column that is already sorted, reverse the ordering
		if ($column_name == $ordering)
		{
			// $ordering_class = 'sorted';

			if ($order_dir == 'desc')
			{
				$order_dir_class = 'akion-android-arrow-dropdown';
				$url->setVar('order_dir', 'asc');
			}
		}

		// $classes[] = 'manage-column';
		// $classes[] = $ordering_class;
		// $classes[] = $order_dir_class;

		$html  = '<td class="manage-column" style="'.$style.'">';
		$html .=    '<a href="'.$url->toString().'"><span>'.$label.'</span></a>&nbsp;<span class="'.$order_dir_class.'"></span>';
		$html .= '</td>';

		return $html;
	}

	/**
	 * Creates the HTML code for pagination
	 *
	 * @param   int      $total        Total amount of records
	 * @param   int      $limitstart   Initial offset
	 * @param   int|null $limit        Records per page
	 *
	 * @return string
	 */
	public static function pagination($total, $limitstart, $limit = null)
	{
		// If no limit has been supplied, fetch it from user options
		if (!$limit)
		{
			$limit = Wordpress::get_page_limit();
		}

		// Avoid division by zero errors if we have no limits
		$total_pages  = 0;
		$current_page = 0;

		if ($limit)
		{
			$total_pages  = max(1, ceil($total / $limit));
			$current_page = ceil(($limitstart + 1) / $limit);
		}

		if (!$current_page)
		{
			$current_page = 1;
		}

		// If the user set the value of 0 it means that no pagination should be applied
		if ($limit === 0)
		{
			$current_page = 1;
			$total_pages  = 1;
		}

		$page_links    = array();
		$disable_first = ($current_page == 1) || ($current_page == 2);
		$disable_prev  = ($current_page == 1);
		$disable_next  = ($current_page == $total_pages);
		$disable_last  = ($current_page == $total_pages) || ($current_page == ($total_pages - 1));

		// Get a reference to the current URL and null some var
		$base_url      = Uri::getInstance();
		$base_url->setVar('paged', null);
		$base_url->setVar('task', null);

		if ($disable_first)
		{
			$page_links[] = '<span class="tablenav-pages-navspan">&laquo;</span>';
		}
		else
		{
			$url = clone $base_url;
			$url->setVar('limitstart', 0);
			$click_url = $url->toString();
			$page_links[] = '<a class="first-page" href="'.$click_url.'"><span>&laquo;</span></a>';
		}

		if ($disable_prev)
		{
			$page_links[] = '<span class="tablenav-pages-navspan">&lsaquo;</span>';
		}
		else
		{
			$new_limit = ($limitstart - $limit);

			if (!$new_limit)
			{
				$new_limit = 0;
			}

			$url = clone $base_url;
			$url->setVar('limitstart', $new_limit);
			$click_url = $url->toString();
			$page_links[] = '<a class="prev-page" href="'.$click_url.'"><span>&lsaquo;</span></a>';
		}

		$html_current_page  = '<input class="current-page" id="current-page-selector" type="text" name="paged" value="'.$current_page.'" size="'.strlen( $total_pages).'"/>';
		$html_current_page .= '<span class="tablenav-paging-text">';

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[]     = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span></span>';

		if ($disable_next)
		{
			$page_links[] = '<span class="tablenav-pages-navspan">&rsaquo;</span>';
		}
		else
		{
			$new_limit = ($limitstart + $limit);

			if ($new_limit > $total)
			{
				$new_limit = $total;
			}

			$url = clone $base_url;
			$url->setVar('limitstart', $new_limit);
			$click_url = $url->toString();
			$page_links[] = '<a class="next-page" href="'.$click_url.'"><span>&rsaquo;</span></a>';
		}

		if ($disable_last)
		{
			$page_links[] = '<span class="tablenav-pages-navspan">&raquo;</span>';
		}
		else
		{
			// Take the second to last page and multiply for the limit, so we will start from the last one
			$new_limit = ($total_pages  - 1 ) * $limit;

			if ($new_limit > $total)
			{
				$new_limit = $total - $limit;
			}

			$url = clone $base_url;
			$url->setVar('limitstart', $new_limit);
			$click_url = $url->toString();
			$page_links[] = '<a class="last-page" href="'.$click_url.'"><span>&raquo;</span></a>';
		}

		$output  = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total ), number_format_i18n( $total ) ) . '</span>';
		$output .= '<span class="pagination-links">'. implode("\n", $page_links ) . '</span>';

		$html    = '<div class="tablenav-pages">'.$output.'</div>';

		return $html;
	}

	public static function getFileSourceForDisplay($path, $highlight = false)
	{
		$filedata = @file_get_contents(ABSPATH . '/' . $path);

		if (!$highlight)
		{
			return htmlentities($filedata);
		}

		$highlightPrefixSuspicious = "%*!*[[###  ";
		$highlightSuffixSuspicious = "  ###]]*!*%";
		$highlightPrefixKnownHack = "%*{{!}}*[[###  ";
		$highlightSuffixKnownHack = "  ###]]*{{!}}*%";

		/** @var string $encodedConfig Defined in the included file */
		require_once ADMINTOOLSWP_PATH . '/app/model/scanner/encodedconfig.php';

		$zipped = pack('H*', $encodedConfig);
		unset($encodedConfig);

		$json_encoded = gzinflate($zipped);
		unset($zipped);

		$new_list = json_decode($json_encoded, true);
		extract($new_list);

		unset($new_list);

		/** @var array $suspiciousWords  Simple array of words that are suspicious */
		/** @var array $knownHackSignatures  Known hack signatures, $signature => $weight */
		/** @var array $suspiciousRegEx  Suspicious constructs' RegEx, $regex => $weight */


		foreach ($suspiciousWords as $word)
		{
			$replacement = $highlightPrefixSuspicious . $word . $highlightSuffixSuspicious;
			$filedata    = str_replace($word, $replacement, $filedata);
		}

		foreach ($knownHackSignatures as $signature => $sigscore)
		{
			$replacement = $highlightPrefixKnownHack . $signature . $highlightSuffixKnownHack;
			$filedata    = str_replace($signature, $replacement, $filedata);
		}

		foreach ($suspiciousRegEx as $pattern => $value)
		{
			$filedata = preg_replace_callback($pattern, function($m) use ($highlightPrefixSuspicious, $highlightSuffixSuspicious) {
				return $highlightPrefixSuspicious . $m[0] . $highlightSuffixSuspicious;
			}, $filedata);
		}

		$filedata = htmlentities($filedata);

		$filedata = str_replace(array(
			$highlightPrefixSuspicious,
			$highlightSuffixSuspicious
		), array(
			'<span style="background: yellow; font-weight: bold; color: red; padding: 2px 4px">',
			'</span>'
		), $filedata);

		$filedata = str_replace(array(
			$highlightPrefixKnownHack,
			$highlightSuffixKnownHack
		), array(
			'<span style="background: red; font-weight: bold; color: white; padding: 2px 4px">',
			'</span>'
		), $filedata);

		return $filedata;
	}
}
