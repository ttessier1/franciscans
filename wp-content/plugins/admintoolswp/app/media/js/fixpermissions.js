/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

if (typeof admintools === 'undefined')
{
    var admintools = {};
}

if (typeof admintools.FixPermissions == 'undefined')
{
	admintools.FixPermissions = {
		'closeMe': function ()
		   {
               parent.admintools.ControlPanel.closeModal();
		   }
	};
}

// This is a tmpl=component view, requiring jQuery is too much complicated for simply submitting a form
document.addEventListener("DOMContentLoaded", function(event) {
    var autoCloseElement = document.getElementById('admintools-fixpermissions-autoclose');

    if (autoCloseElement)
    {
        window.setTimeout(admintools.FixPermissions.closeMe, 3000);
    }
    else
    {
        document.forms.adminForm.submit();
    }
});