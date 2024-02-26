/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

jQuery(document).ready(function ($)
{
	var autoCloseElement = $('#admintools-databasetools-autoclose');

	if (!autoCloseElement.length)
	{
		document.forms.adminForm.submit();
	}
});
