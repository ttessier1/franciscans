/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

window.atwpListItemTask = function ( id, task ) {
    var f = document.getElementById('admintoolswpForm'),
        i = 0, cbx,
        cb = f[ id ];

    if ( !cb ) return false;

    while ( true ) {
        cbx = f[ 'cb-select-' + i ];

        if ( !cbx ) break;

        cbx.checked = false;

        i++;
    }

    cb.checked = true;
    window.atwpSubmitform( task );

    return false;
};

window.atwpSubmitform = function(task, form, validate) {

    if (!form) {
        form = document.getElementById('admintoolswpForm');
    }

    if (task) {
        form.task.value = task;
    }

    // Toggle HTML5 validation
    form.noValidate = !validate;
    form.setAttribute('novalidate', !validate);

    // Submit the form.
    // Create the input type="submit"
    var button = document.createElement('input');
    button.style.display = 'none';
    button.type = 'submit';

    // Append it and click it
    form.appendChild(button).click();

    // If "submit" was prevented, make sure we don't get a build up of buttons
    form.removeChild(button);
};
