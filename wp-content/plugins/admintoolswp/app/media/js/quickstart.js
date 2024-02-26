/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/**
 * Setup
 */
if (typeof(akeeba) == 'undefined')
{
	var akeeba = {};
}

if (typeof(akeeba.jQuery) == 'undefined')
{
	akeeba.jQuery = jQuery.noConflict();
}

if (typeof(admintools) == 'undefined')
{
    var admintools = {};
}

// Object initialization
if (typeof admintools.ImportExport == 'undefined')
{
	admintools.QuickStart = {
		"myIP": "",
		"youWantToBreakYourSite": function ()
		{
			akeeba.jQuery("#youhavebeenwarnednottodothat").hide();
			akeeba.jQuery("#adminForm").show();
		}
	};
}

akeeba.jQuery(document).ready(function ($)
{
});
