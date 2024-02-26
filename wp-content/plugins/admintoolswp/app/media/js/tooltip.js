/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

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

if (typeof akeeba.Tooltip == 'undefined')
{
    akeeba.Tooltip = {};
}

if (typeof Math.uuid == 'undefined')
{
    Math.uuid = (function ()
    {
        // Private array of chars to use
        var CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('');

        return function (len, radix)
        {
            var chars = CHARS, uuid = [];
            radix     = radix || chars.length;

            if (len)
            {
                // Compact form
                for (var i = 0; i < len; i++)
                {
                    uuid[i] = chars[0 | Math.random() * radix];
                }
            }
            else
            {
                // rfc4122, version 4 form
                var r;

                // rfc4122 requires these characters
                uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
                uuid[14] = '4';

                // Fill in random data.  At i==19 set the high bits of clock sequence as
                // per rfc4122, sec. 4.1.5
                for (var i = 0; i < 36; i++)
                {
                    if (!uuid[i])
                    {
                        r       = 0 | Math.random() * 16;
                        uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                    }
                }
            }

            return uuid.join('');
        };
    })();
}

akeeba.Tooltip.enableFor = function (el, clickToStick)
{
	if ((typeof clickToStick == "undefined") || (clickToStick === null))
	{
		clickToStick = true;
	}

	if ((typeof el == 'object') && NodeList.prototype.isPrototypeOf(el))
	{
		for (var i = 0; i < el.length; i++)
		{
			var e = el[i];

			akeeba.Tooltip.enableFor(e, clickToStick);
		}

		return;
	}

	akeeba.jQuery(el).on('mouseenter', akeeba.Tooltip.onMouseEnter);
    akeeba.jQuery(el).on('mouseleave', akeeba.Tooltip.onMouseLeave);

	if (clickToStick)
	{
		akeeba.jQuery(el).on('click', akeeba.Tooltip.onClick);
	}
};

/**
 * Converts the contents of the "title" attribute into a simple CSS tooltip.
 *
 * @param   {HTMLElement}  el  The element which we're applying the tooltip for.
 */
akeeba.Tooltip.simpleTooltip = function(el)
{
	if (!el.hasAttribute('title'))
	{
		return;
	}

	var content = el.getAttribute('title');
	var position = 'right';

	if (el.hasAttribute('data-akeeba-tooltip-position'))
	{
		position = el.getAttribute('data-akeeba-tooltip-position');
	}

	// Mark the element as having a tooltip
	akeeba.jQuery(el).addClass('akeeba-hasTooltip');

	// Create the tooltip text div
	var elTooltip = document.createElement('div');
	elTooltip.className = 'akeeba-tooltip-text akeeba-tooltip-text-' + position;
	elTooltip.innerHTML = content;

	// Append the tooltip to the target element
	el.appendChild(elTooltip);

	// Remove the title attribute from the target element
	el.removeAttribute('title');
};

/**
 * Handles the mouseenter event for a target element (mousing over it will render the tooltip). Basically, shows the
 * tooltip.
 *
 * @param   {Event}  e  The event we're handling
 */
akeeba.Tooltip.onMouseEnter = function (e)
{
	// If there's no tooltip content quit
	if (!e.target.hasAttribute('data-content'))
	{
		return;
	}

	// Get the tooltip UUID stored in the target element. If none exists then create one.
	var uuid = '';

	if (!e.target.hasAttribute('data-tooltip-uuid'))
	{
		uuid = Math.uuid();
		e.target.setAttribute('data-tooltip-uuid', uuid);
	}
	else
	{
		uuid = e.target.getAttribute('data-tooltip-uuid');
	}

	// Do I have an open tooltip?
	var oldTooltip = document.getElementById('akeeba-tooltip-' + uuid);

	if (oldTooltip != null)
	{
		return;
	}

	// Get the tooltip positioning
	var pos           = e.target.getAttribute('data-position') || "center bottom",
		posHorizontal = pos.split(" ")[0],
		posVertical   = pos.split(" ")[1];

	// Create the outer tooltip wrapper element
	var tooltipWrapper           = document.createElement('div');
	tooltipWrapper.className     = 'akeeba-popover';
	tooltipWrapper.id            = 'akeeba-tooltip-' + uuid;
	tooltipWrapper.style.display = 'block';

	// Assign the position as a class to the tooltip wrapper (required by BS to render the arrow correctly)
	tooltipWrapper.className += ' ' + pos;

	// Add the pointed arrow to the tooltip
	tooltipWrapper.insertAdjacentHTML('afterbegin', '<div class="akeeba-arrow"></div>');

	// Create the tooltip inner wrapper
	var tooltipInner       = document.createElement('div');
	tooltipInner.className = 'akeeba-popover-inner';
	tooltipWrapper.appendChild(tooltipInner);

	// If we have a tooltip title, render it
	if (e.target.hasAttribute('data-original-title'))
	{
		var elTitle       = document.createElement('h3');
		elTitle.className = 'akeeba-popover-title';
		elTitle.innerHTML = e.target.getAttribute('data-original-title');
		tooltipInner.appendChild(elTitle);
	}

	// Render the tooltip content
	var elContent       = document.createElement('div');
	elContent.className = 'akeeba-popover-content';
	elContent.innerHTML = e.target.getAttribute('data-content');
	tooltipInner.appendChild(elContent);

	// Append the tooltip to the HTML body
	document.body.appendChild(tooltipWrapper);

	// Position the tooltip relative to the target element
	akeeba.Tooltip.positionAt(e.target, tooltipWrapper, posHorizontal, posVertical);
};

/**
 * Handles the mouseleave event for a target element (mousing out of it will close the tooltip). Basically, hides the
 * tooltip unless it's marked as 'do not hide'.
 *
 * @param   {Event}  e  The event we're handling
 */
akeeba.Tooltip.onMouseLeave = function (e)
{
	// If this marked as "no close" do nothing
	if (e.target.hasAttribute('data-tooltip-noclose'))
	{
		return;
	}

	// Close the tooltip
	akeeba.Tooltip.hideTooltip(e.target);

};

/**
 * Handles the click event on a target element. Clicking on the target element while the tooltip is open will mark the
 * tooltip as "no close", i.e. mousing out of the target will do nothing. A second click will, however, reset this
 * behaviour and let the tooltip disappear.
 *
 * @param e
 */
akeeba.Tooltip.onClick = function (e)
{
	// If there is no tooltip UUID attached to the element we can do nothing
	if (!e.target.hasAttribute('data-tooltip-uuid'))
	{
		return;
	}

	// If it's already marked as "no close" this is the second click. Therefore it's time to unset the "no close"
	// attribute.
	if (e.target.hasAttribute('data-tooltip-noclose'))
	{
		e.target.removeAttribute('data-tooltip-noclose');

		return;
	}

	// Is the tooltip actually open?
	var uuid     = e.target.getAttribute('data-tooltip-uuid');
	var elRemove = document.getElementById('akeeba-tooltip-' + uuid);

	if (elRemove == null)
	{
		return;
	}

	// This is the first click on the element. Mark the tooltip as "no close".
	e.target.setAttribute('data-tooltip-noclose', 1);
};

/**
 * Hides the tooltip related to a target
 *
 * @param   {HTMLElement}  target  The element which had triggered the display of the tooltip
 */
akeeba.Tooltip.hideTooltip = function (target)
{
	// If there is no tooltip UUID attached to the element we can do nothing
	if (!target.hasAttribute('data-tooltip-uuid'))
	{
		return;
	}

	// Get the tooltip UUID
	var uuid = target.getAttribute('data-tooltip-uuid');

	// Remove the tooltip element from the HTML body
	var elRemove = document.getElementById('akeeba-tooltip-' + uuid);

	if (!elRemove)
	{
		return;
	}

	document.body.removeChild(elRemove);
};

/**
 * Positions the tooltip.
 *
 * @param {object} parent - The trigger of the tooltip.
 * @param {object} tooltip - The tooltip itself.
 * @param {string} posHorizontal - Desired horizontal position of the tooltip relatively to the trigger (left/center/right)
 * @param {string} posVertical - Desired vertical position of the tooltip relatively to the trigger (top/center/bottom)
 *
 */
akeeba.Tooltip.positionAt = function (parent, tooltip, posHorizontal, posVertical)
{
	var parentCoords = parent.getBoundingClientRect(), left, top;
	var dist         = 10;

	switch (posHorizontal)
	{
		case "left":
			left = parseInt(parentCoords.left) - dist - tooltip.offsetWidth;
			if (parseInt(parentCoords.left) - tooltip.offsetWidth < 0)
			{
				left = dist;
			}
			break;

		case "right":
			left = parentCoords.right + dist;
			if (parseInt(parentCoords.right) + tooltip.offsetWidth > document.documentElement.clientWidth)
			{
				left = document.documentElement.clientWidth - tooltip.offsetWidth - dist;
			}
			break;

		default:
		case "center":
			left = parseInt(parentCoords.left) + ((parent.offsetWidth - tooltip.offsetWidth) / 2);
	}

	switch (posVertical)
	{
		case "center":
			top = (parseInt(parentCoords.top) + parseInt(parentCoords.bottom)) / 2 - tooltip.offsetHeight / 2;
			break;

		case "bottom":
			top = parseInt(parentCoords.bottom) + dist;
			break;

		default:
		case "top":
			top = parseInt(parentCoords.top) - tooltip.offsetHeight - dist;
	}

	left = (left < 0) ? parseInt(parentCoords.left) : left;
	top  = (top < 0) ? parseInt(parentCoords.bottom) + dist : top;

	tooltip.style.left = left + "px";
	tooltip.style.top  = top + pageYOffset + "px";
};
