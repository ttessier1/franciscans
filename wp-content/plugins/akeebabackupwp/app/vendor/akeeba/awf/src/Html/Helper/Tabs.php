<?php
/**
 * @package   awf
 * @copyright Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Awf\Html\Helper;

use Awf\Html\AbstractHelper;

/**
 * An abstraction around Bootstrap tabs / pills
 *
 * @since 1.1.0
 */
class Tabs extends AbstractHelper
{
	/**
	 * Start a new tabbed area
	 *
	 * @param   boolean  $pills  Should I use the pill navigation style? Otherwise tabs will be used.
	 *
	 * @return  string  The HTML
	 */
	public function start(bool $pills = false): string
	{
		$type = $pills ? 'pills' : 'nav';

		return <<< HTML
<ul class="nav nav-$type">
HTML;

	}

	/**
	 * Add one more tab/pill in the navigation section of the tabbed area
	 *
	 * @param   string  $id     The HTML ID of the tab content area opened by this tab/pill
	 * @param   string  $title  The title of the tab/pill
	 *
	 * @return  string  The HTML
	 */
	public function addNav(string $id, string $title): string
	{
		return <<< HTML
	<li><a href="#$id" data-toggle="tab">$title</a></li>
HTML;

	}

	/**
	 * Closes the navigation area of the tabs and starts the content area
	 *
	 * @return  string  The HTML
	 */
	public function startContent(): string
	{
		return <<< HTML
</ul>
<div class="tab-content">
 	<div style="display:none">
HTML;

	}

	/**
	 * Starts the content section of a tab
	 *
	 * @param   string   $id      The HTML ID of this tab content. Must match what you previously used in addNav
	 * @param   boolean  $active  Is this tab active by default?
	 * @param   boolean  $fade    Should we use a fade transition effect?
	 *
	 * @return  string  The HTML
	 */
	public function tab(string $id, bool $active = false, bool $fade = false): string
	{
		$activeString = $active ? 'active' . ($fade ? ' in' : '') : '';
		$fadeString = $fade ? 'fade' : '';

		return <<< HTML
	<div class="tab-pane $activeString $fadeString" id="$id">
HTML;

	}

	/**
	 * Ends the tabbed area
	 *
	 * @return  string  The HTML
	 */
	public function end(): string
	{
		return <<< HTML
	</div>
</div>
HTML;

	}
} 
