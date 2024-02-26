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
	admintools.ImportExport = {
		'onTemplateExportChange': function ()
		   {
			   var emailTemplateWarning = akeeba.jQuery('#emailtemplateWarning');
			   var exportdataemailtemplates = akeeba.jQuery('#exportdataemailtemplates');

			   if (exportdataemailtemplates.val() == 1)
			   {
				   emailTemplateWarning.show();
			   }
			   else
			   {
				   emailTemplateWarning.hide();
			   }
		   }
	};
}

akeeba.jQuery(document).ready(function ($)
{
	var exportTemplates = akeeba.jQuery('#exportdataemailtemplates');
	exportTemplates.change(function (e){
		admintools.ImportExport.onTemplateExportChange(e);
	});
});
