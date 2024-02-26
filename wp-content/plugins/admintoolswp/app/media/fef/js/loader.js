/*!
 * Akeeba Frontend Framework (FEF)
 *
 * @package   fef
 * @copyright (c) 2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

window.akeeba = window.akeeba || {};

/**
 * Dependency-based script execution.
 *
 * Use:
 * `akeeba.Loader.add('objectName', functionName)`
 * Executes functionName() when window.objectName is made available.
 *
 * `akeeba.Loader.add(['object1', 'object2'], functionName)`
 * Executes functionName() when both window.object1 and window.object2 are made available.
 *
 * This works by installing a timer handler which triggers every 200msec. When the global objects specified are made
 * available (they are no longer undefined) the callback is executed. This lets you convert blocking inline code into
 * async. No matter which order or when the objects are loaded by each script file the dependent callbacks will end up
 * executing without throwing errors.
 */
(function (akeeba, document)
{
    "use strict";

    /**
     * Has the DOM been already loaded?
     *
     * @type {boolean}
     */
    var afterDocumentLoad = false;

    /**
     * List of dependencies and their callbacks.
     *
     * @type {*[]}
     */
    var listOfDeps = [];

    /**
     * JavaScript timer handler. This is what checks my dependencies.
     *
     * @type {null}
     */
    var timerHandler = null;

    /**
     * Is the timer temporarily suspended while I'm evaluating the callbacks?
     *
     * @type {boolean}
     */
    var paused = false;

    /**
     * The dependency checker's period, in milliseconds. Values between 100 and 400 work best. Yeah, this is hardcoded.
     *
     * @type {number}
     */
    const periodInMsec = 200;

    /**
     * First installation of the timer on DOM load event.
     *
     * The loader will be inert before this function is called.
     */
    function firstInstallTimer()
    {
        afterDocumentLoad = true;

        installTimer();
    }

    /**
     * Conditionally start the timer.
     *
     * This will only install the timer after the DOM has finished loading and if it's not already running.
     */
    function installTimer()
    {
        if (afterDocumentLoad && (timerHandler === null))
        {
            timerHandler = setInterval(dependencyCheckHandler, periodInMsec);
        }
    }

    function isFulfilled(depName)
    {
        var bits   = depName.split(".");
        var myRoot = window;

        for (var i = 0; i < bits.length; i++)
        {
            if (typeof myRoot !== "object")
            {
                return false;
            }

            if (typeof myRoot[bits[i]] === "undefined")
            {
                return false;
            }

            myRoot = myRoot[bits[i]];
        }

        return true;
    }

    /**
     * Timer event handler.
     *
     * This goes through a list of object names. If the object is defined in the window document it will run the
     * associated callback.
     */
    function dependencyCheckHandler()
    {
        /**
         * We pause this timer while we're evaluating dependencies. This prevents triggering the same blocking callbacks
         * multiple times if their execution is longer than our timeout.
         *
         * This block checks if the timer is temporarily paused.
         */
        if (paused)
        {
            return;
        }

        // If there are no more dependencies to check I can unset the timer to save CPU cycles.
        if (!listOfDeps.length)
        {
            clearInterval(timerHandler);
            timerHandler = null;

            return;
        }

        // Pause the timer (see above)
        paused = true;

        // Prepare to collect dependencies I didn't run successfully yet.
        var collect = [];

        // Loop all dependencies
        for (var i = 0; i < listOfDeps.length; i++)
        {
            // Unwrap the dependency entry
            var depEntry  = listOfDeps[i];
            var depName   = depEntry[0];
            var callback  = depEntry[1];
            var fulfilled = false;

            // Evaluate the object dependencies
            if (typeof depName === "string")
            {
                // String dependency. Check for one object only.
                fulfilled = isFulfilled(depName);
            }
            else
            {
                // Array dependency. Check for multiple objects.
                fulfilled = true;

                for (var j = 0; j < depName.length; j++)
                {
                    fulfilled = fulfilled && isFulfilled(depName[j]);
                }
            }

            // If the dependencies are not fulfilled add the definition back to the list and move on
            if (!fulfilled)
            {
                collect[collect.length] = [depName, callback];

                continue;
            }

            // Fulfilled dependencies.
            callback();
        }

        // Swap the list of dependecies.
        listOfDeps = collect;
        // Unpause this timer.
        paused     = false;
    }

    akeeba.Loader = akeeba.Loader || {};

    akeeba.Loader.add = function (dependency, callback)
    {
        listOfDeps[listOfDeps.length] = [dependency, callback];

        installTimer();
    }

    // Schedule myself to run onDOMContentLoaded
    document.addEventListener("DOMContentLoaded", firstInstallTimer, false);
    window.addEventListener("load", firstInstallTimer, false);
})(akeeba, document);