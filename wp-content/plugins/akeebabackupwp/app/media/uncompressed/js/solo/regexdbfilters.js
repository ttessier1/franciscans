/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Object initialisation
if (typeof akeeba == "undefined")
{
    var akeeba = {};
}

if (typeof akeeba.Regexdbfilters == "undefined")
{
    akeeba.Regexdbfilters = {
        currentRoot: null
    }
}

/**
 * Change the active root
 */
akeeba.Regexdbfilters.activeRootChanged = function ()
{
    var activeRootEl = document.getElementById("active-root");
    akeeba.Regexdbfilters.load(
        activeRootEl.options[activeRootEl.selectedIndex].value
    );
};

/**
 * Load data from the server
 *
 * @param   new_root  The root to load data for
 */
akeeba.Regexdbfilters.load = function load(new_root)
{
    var data = {
        root: new_root,
        verb: "list"
    };

    var request = {
        akaction: JSON.stringify(data)
    };
    akeeba.System.doAjax(request, function (response)
    {
        akeeba.Regexdbfilters.render(response);
    }, null, false, 15000);
};

/**
 * Render the data in the GUI
 *
 * @param   data
 */
akeeba.Regexdbfilters.render = function (data)
{
    var tbody       = document.getElementById("ak_list_contents");
    tbody.innerHTML = "";

    for (var counter = 0; counter < data.length; counter++)
    {
        var def = data[counter];

        akeeba.Regexdbfilters.addRow(def, tbody);
    }

    var newdef = {
        type: "",
        item: ""
    };

    akeeba.Regexdbfilters.addNewRow(tbody);
};

/**
 * Adds a row to the GUI
 *
 * @param   def             Filter definition
 * @param   append_to_here  Element to append the row to
 */
akeeba.Regexdbfilters.addRow = function (def, append_to_here)
{
    var trow = document.createElement("tr");
    append_to_here.appendChild(trow);

    // Is this an existing filter or a new one?
    var edit_icon_class = "akion-edit";

    if (def.item == "")
    {
        edit_icon_class = "akion-plus";
    }

    var td_buttons = document.createElement("td");
    var elEdit     = document.createElement("span");
    var elDelete   = document.createElement("span");

    elEdit.className = "table-icon-container akeeba-btn--teal--mini edit ak-toggle-button";
    elEdit.insertAdjacentHTML("beforeend", "<span class=\"" + edit_icon_class + "\"></span>");

    akeeba.System.addEventListener(elEdit, "click", function ()
    {
        // Create the drop down
        var known_filters = ["regextables", "regextabledata"];
        var mySelect      = document.createElement("select");
        mySelect.setAttribute("name", "type");
        mySelect.className = "type-select";

        for (var i = 0; i < known_filters.length; i++)
        {
            var filter_name = known_filters[i];

            var type_translation_key = "COM_AKEEBA_DBFILTER_TYPE_" + String(filter_name).toUpperCase();
            var type_localized       = akeeba.System.Text._(type_translation_key);
            var selected             = false;

            var elOption = document.createElement("option");
            elOption.setAttribute("value", filter_name);
            elOption.textContent = type_localized;

            if (filter_name == def.type)
            {
                elOption.setAttribute("selected", "selected");
            }

            mySelect.appendChild(elOption);
        }

        // Switch the type span with the drop-down
        trow.querySelector("td.ak-type span").style.display = "none";
        trow.querySelector("td.ak-type").appendChild(mySelect);

        // Create the edit box
        var myEditBox = document.createElement("input");
        myEditBox.setAttribute("type", "text");
        myEditBox.setAttribute("name", "item");
        myEditBox.setAttribute("size", "100");
        myEditBox.value = def.item;

        // Switch the item tt with the input box
        trow.querySelector("td.ak-item tt").style.display = "none";
        trow.querySelector("td.ak-item").appendChild(myEditBox);
        // Hide the edit/delete buttons, add save/cancel buttons
        var firstTD                                        = trow.children[0];
        firstTD.querySelector("span.edit").style.display   = "none";
        firstTD.querySelector("span.delete").style.display = "none";
        var elSave                                         = document.createElement("span");
        firstTD.appendChild(elSave);

        elSave.className = "table-icon-container akeeba-btn--teal--mini save ak-toggle-button";
        elSave.insertAdjacentHTML("beforeend", "<span class=\"akion-checkmark\"></span>");

        akeeba.System.addEventListener(elSave, "click", function ()
        {
            var firstTD                                        = trow.children[0];
            firstTD.querySelector("span.delete").style.display = "none";
            var new_type                                       = mySelect.options[mySelect.selectedIndex].value;
            var new_item                                       = myEditBox.value;

            if (trim(new_item) == "")
            {
                // Empty item detected. It is equivalent to delete or cancel.
                if (def.item == "")
                {
                    akeeba.System.triggerEvent(firstTD.querySelector("span.cancel"), "click");
                    return;
                }
                else
                {
                    akeeba.System.triggerEvent(firstTD.querySelector("span.delete"), "click");
                    return;
                }
            }

            var elActiveRoot = document.getElementById("active_root");
            var new_data     = {
                verb: "set",
                type: new_type,
                node: new_item,
                root: elActiveRoot.options[elActiveRoot.selectedIndex].value
            };
            console.debug(new_data);

            akeeba.Fsfilters.toggle(new_data, this, function (response, caller)
            {
                // Now that we saved the new filter, delete the old one
                var haveToDelete = (def.item != "") && (def.type != "") &&
                    ((def.item != new_item) || (def.type != new_type));
                var addedNewItem = (def.item == "") || (def.type == "");

                var firstTD = trow.children[0];

                if (def.item == "")
                {
                    var elThisEdit       = firstTD.querySelector("span.edit").firstChild;
                    elThisEdit.className = elThisEdit.className.replace("akion-plus", "akion-edit");
                }

                new_data.type                                     = def.type;
                new_data.node                                     = def.item;
                def.type                                          = new_type;
                def.item                                          = new_item;
                var type_translation_key                          = "COM_AKEEBA_DBFILTER_TYPE_" +
                    String(def.type).toUpperCase();
                trow.querySelector("td.ak-type span").textContent = akeeba.System.Text._(type_translation_key);
                trow.querySelector("td.ak-item tt").textContent   = escapeHTML(def.item);
                akeeba.System.triggerEvent(firstTD.querySelector("span.cancel"), "click");

                if (haveToDelete)
                {
                    new_data.verb = "remove";
                    akeeba.Fsfilters.toggle(new_data, this, function (response, caller)
                    {
                    }, false);
                }
                else if ((def.item != new_item) || (def.type != new_type) || addedNewItem)
                {
                    akeeba.Regexdbfilters.addNewRow(append_to_here);
                }
            }, false);
        });

        var elCancel = document.createElement("span");
        firstTD.appendChild(elCancel);

        elCancel.className = "table-icon-container akeeba-btn--orange--mini cancel ak-toggle-button";
        elCancel.insertAdjacentHTML("beforeend", "<span class=\"akion-close\"></span>");

        akeeba.System.addEventListener(elCancel, "click", function ()
        {
            var firstTD = trow.children[0];

            // Cancel changes; remove editing GUI elements, show the original elements
            var elRemoveMe = firstTD.querySelector("span.save");
            elRemoveMe.parentNode.removeChild(elRemoveMe);

            elRemoveMe = firstTD.querySelector("span.cancel");
            elRemoveMe.parentNode.removeChild(elRemoveMe);

            firstTD.querySelector("span.edit").style.display = "inline-block";
            if (def.item != "")
            {
                firstTD.querySelector("span.delete").style.display = "inline-block";
            }

            mySelect.parentNode.removeChild(mySelect);
            trow.querySelector("td.ak-type span").style.display = "inline-block";
            myEditBox.parentNode.removeChild(myEditBox);
            trow.querySelector("td.ak-item tt").style.display = "inline-block";
        });
    });

    elDelete.className = "table-icon-container akeeba-btn--red--mini delete ak-toggle-button";
    elDelete.insertAdjacentHTML("beforeend", "<span class=\"akion-trash-a\"></span>");

    akeeba.System.addEventListener(elDelete, "click", function ()
    {
        var elActiveRoot = document.getElementById("active_root");
        var new_data     = {
            verb: "remove",
            type: def.type,
            node: def.item,
            root: elActiveRoot.options[elActiveRoot.selectedIndex].value
        };
        akeeba.Fsfilters.toggle(new_data, this, function (response, caller)
        {
            trow.remove();

            if (def.item == "")
            {
                akeeba.Regexdbfilters.addNewRow(append_to_here);
            }
        }, false);
    });

    td_buttons.appendChild(elEdit);
    td_buttons.appendChild(elDelete);
    trow.appendChild(td_buttons);

    // Hide the delete button on new rows
    if (def.item == "")
    {
        td_buttons.querySelector("span.delete").style.display = "none";
    }

    // Filter type and filter item rows
    var type_translation_key = "COM_AKEEBA_DBFILTER_TYPE_" + String(def.type).toUpperCase();
    var type_localized       = akeeba.System.Text._(type_translation_key);
    if (def.type == "")
    {
        type_localized = "";
    }

    var tdType       = document.createElement("td");
    tdType.className = "ak-type";
    tdType.innerHTML = "<span>" + type_localized + "</span>";
    trow.appendChild(tdType);

    var tdRoot       = document.createElement("td");
    tdRoot.className = "ak-item";
    tdRoot.innerHTML = "<tt>" + ((def.item == null) ? "" : escapeHTML(def.item)) + "</tt>";
    trow.appendChild(tdRoot);
};

/**
 * Add a new row to the GUI
 *
 * @param   append_to_here  Element where to append the row
 */
akeeba.Regexdbfilters.addNewRow = function (append_to_here)
{
    var newdef = {
        type: "",
        item: ""
    };

    akeeba.Regexdbfilters.addRow(newdef, append_to_here);
};

akeeba.System.documentReady(function ()
{
    akeeba.System.addEventListener("active_root", "change", akeeba.Regexdbfilters.activeRootChanged);
    akeeba.Regexdbfilters.render(akeeba.System.getOptions("akeeba.RegExDatabaseFilters.guiData", {}));
});