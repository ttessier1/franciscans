/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

// Object initialization
if (typeof(akeeba) === 'undefined')
{
    var akeeba = {};
}

if (typeof(akeeba.jQuery) === 'undefined')
{
    akeeba.jQuery = jQuery.noConflict();
}

if (typeof admintools === 'undefined')
{
    var admintools = {};
}

if (typeof admintools.AdvancedWpConfig == 'undefined')
{
    admintools.AdvancedWpConfig = {};

    admintools.AdvancedWpConfig.enablePopoverFor = function (el)
    {
        if ((typeof el == 'object') && NodeList.prototype.isPrototypeOf(el))
        {
            for (var i = 0; i < el.length; i++)
            {
                var e = el[i];

                admintools.AdvancedWpConfig.enablePopoverFor(e);
            }

            return;
        }

        akeeba.Tooltip.enableFor(el);
    };
}

(function ($)
{
    $(document).ready(function ()
    {
        // Enable popovers
        admintools.AdvancedWpConfig.enablePopoverFor($('[rel="akeeba-sticky-tooltip"]'));
    });
})(akeeba.jQuery);
