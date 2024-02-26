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

// Object initialization
if (typeof admintools.ControlPanel === 'undefined')
{
    admintools.ControlPanel = {
        'modal': null,
        'myIP': '',
        'plugin_url': '',
        'fixPerms': function () {},
        'closeModal': function() {}
    };
}

admintools.ControlPanel.showUnblockMyself = function()
{
    jQuery.ajax(admintools.ControlPanel.plugin_url + '&view=controlpanel&task=selfblocked',
        {
            data: {
                ip: admintools.ControlPanel.myIP
            },

            success: function (msg)
            {
                // Get rid of junk before and after data
                var match  = msg.match(/###([\s\S]*?)###/);
                var result = match[1];

                if (result == 1)
                {
                    var $selfBlocked       = jQuery('#selfBlocked');
                    var $selfBlockedAnchor = $selfBlocked.find('a');
                    $selfBlockedAnchor.attr('href', $selfBlockedAnchor.attr('href') + '&ip=' + admintools.ControlPanelGraphs.myIP);
                    $selfBlocked.show();
                }
            }
        });
};

admintools.ControlPanel.showUpdateInformation = function(force)
{
    var forceString = ((typeof force === 'undefined') || (force === false)) ? '0' : '1';

    jQuery.ajax(admintools.ControlPanel.plugin_url + '&view=controlpanel&task=updateinfo&force=' + forceString, {
        success: function (msg, textStatus, jqXHR)
        {
            // Get rid of junk before and after data
            var match = msg.match(/###([\s\S]*?)###/);
            data = match[1];

            if (data.length)
            {
                var elContainer = document.getElementById('updateNotice');
				elContainer.innerHTML = data;
            }
        }
    });
};

admintools.ControlPanel.fixPerms = function()
{
    admintools.ControlPanel.modal = admintools.Modal.open({
        width: "600",
        height: "250",
        iframe: akeeba.jQuery('#fixperms').prop('href')
    });

    return false;
};

admintools.ControlPanel.closeModal = function()
{
    if (!admintools.ControlPanel.modal)
    {
        return;
    }

    admintools.ControlPanel.modal.close();
    admintools.ControlPanel.modal = null;
};

akeeba.jQuery(document).ready(function ($)
{
    // Show self-unblock button if necessary
    admintools.ControlPanel.showUnblockMyself();

    // Show the component update information when required
    admintools.ControlPanel.showUpdateInformation();

    $('#fixperms').click(admintools.ControlPanel.fixPerms);
});
