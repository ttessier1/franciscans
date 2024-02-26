/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

// Object initialization
if (typeof admintools === "undefined")
{
    var admintools = {};
}

// Object initialization
if (typeof admintools.ControlPanelGraphs === "undefined")
{
    admintools.ControlPanelGraphs = {
        "graph": {
            "from":         "",
            "to":           "",
            "exceptPoints": [],
            "subsPoints":   [],
            "typePoints":   []
        },
        "plots": [null, null]
    };
}

admintools.ControlPanelGraphs.loadGraphs = function ()
{
    function padDigits(number, digits)
    {
        return Array(Math.max(digits - String(number).length + 1, 0)).join(0) + number;
    }

    // Get the From date
    admintools.ControlPanelGraphs.graph.from = document.getElementById("admintools_graph_datepicker").value;

    // Calculate the To date
    var thatDay                            = new Date(admintools.ControlPanelGraphs.graph.from);
    thatDay                                = new Date(thatDay.getTime() + 30 * 86400000);
    thatDay                                =
        new Date(
            padDigits(thatDay.getUTCFullYear(), 4) + "-" + padDigits(thatDay.getUTCMonth() + 1, 2) + "-" + padDigits(
            thatDay.getUTCDate(), 2));
    admintools.ControlPanelGraphs.graph.to = thatDay.toISOString().slice(0, 10);

    // Clear the data arrays
    admintools.ControlPanelGraphs.graph.lineLabels   = [];
    admintools.ControlPanelGraphs.graph.pieLabels    = [];
    admintools.ControlPanelGraphs.graph.exceptPoints = [];
    admintools.ControlPanelGraphs.graph.typePoints   = [];

    // Remove the charts and show the spinners
    var $akExceptionsPerTypePieChart = jQuery("#admintoolsExceptionsPieChart");
    $akExceptionsPerTypePieChart.hide();
    jQuery("#akthrobber2").show();

    var $akExceptionsLineChart = jQuery("#admintoolsExceptionsLineChart");
    $akExceptionsLineChart.hide();
    jQuery("#akthrobber").show();

    admintools.ControlPanelGraphs.loadExceptionsLineGraph();
};

admintools.ControlPanelGraphs.loadExceptionsLineGraph = function ()
{
    var baseUrl = admintools.ControlPanel ? admintools.ControlPanel.plugin_url : admintools.ControlPanelGraphs.plugin_url;
    var url = baseUrl + "&view=SecurityExceptions&task=getByDate&datefrom=" + admintools.ControlPanelGraphs.graph.from + "&dateto=" + admintools.ControlPanelGraphs.graph.to + "&format=json";

    jQuery.get(url, function (data)
    {
        // Get rid of junk before and after data
        var match = data.match(/###([\s\S]*?)###/);
        data      = JSON.parse(match[1]);

        jQuery.each(data, function (index, item)
        {
            admintools.ControlPanelGraphs.graph.lineLabels.push(item.date);
            admintools.ControlPanelGraphs.graph.exceptPoints.push(
                parseInt(item.exceptions * 100) / 100
            );
        });

        jQuery("#akthrobber").hide();

        var $akExceptionsLineChart = jQuery("#admintoolsExceptionsLineChart");

        $akExceptionsLineChart.show();

        if (admintools.ControlPanelGraphs.graph.exceptPoints.length === 0)
        {
            $akExceptionsLineChart.hide();
            jQuery("#admintoolsExceptionsLineChartNoData").show();
        }
        else
        {
            admintools.ControlPanelGraphs.renderExceptionsLineGraph();
        }

        admintools.ControlPanelGraphs.loadExceptionsPieGraph();
    });
};

admintools.ControlPanelGraphs.loadExceptionsPieGraph = function ()
{
    var baseUrl = admintools.ControlPanel ? admintools.ControlPanel.plugin_url : admintools.ControlPanelGraphs.plugin_url;
    var url = baseUrl + "&view=SecurityExceptions&task=getByType&datefrom=" + admintools.ControlPanelGraphs.graph.from + "&dateto=" + admintools.ControlPanelGraphs.graph.to + "&format=json";

    jQuery.get(url, function (data)
    {
        // Get rid of junk before and after data
        var match = data.match(/###([\s\S]*?)###/);
        data      = JSON.parse(match[1]);

        jQuery.each(data, function (index, item)
        {
            admintools.ControlPanelGraphs.graph.pieLabels.push(item.reason);
            admintools.ControlPanelGraphs.graph.typePoints.push(parseInt(item.exceptions * 100) / 100);
        });

        console.log();

        jQuery("#akthrobber2").hide();

        var $akExceptionsPerTypePieChart = jQuery("#admintoolsExceptionsPieChart");
        $akExceptionsPerTypePieChart.show();

        if (admintools.ControlPanelGraphs.graph.typePoints.length === 0)
        {
            $akExceptionsPerTypePieChart.hide();
            jQuery("#admintoolsExceptionsPieChartNoData").show();
        }
        else
        {
            admintools.ControlPanelGraphs.renderExceptionsPieGraph();
        }
    });
};

admintools.ControlPanelGraphs.renderExceptionsPieGraph = function ()
{
    new Chart(document.getElementById("admintoolsExceptionsPieChart"), {
        type:    "doughnut",
        data:    {
            labels:   admintools.ControlPanelGraphs.graph.pieLabels,
            datasets: [
                {
                    backgroundColor: [
                        "#40B5B8",
                        "#E2363C",
                        "#514F50",
                        "#92CF3B",
                        "#F0AD4E",
                        "#EFEFEF",
                        "yellow",
                        "green",
                        "purple"
                    ],
                    data:            admintools.ControlPanelGraphs.graph.typePoints,
                    fill:            false,
                    borderColor:     "rgb(75, 192, 192)",
                    tension:         0.1
                }
            ]
        },
        options: {
            plugins: {
                legend: {
                    position: "right"
                }
            }
        }
    });
};

admintools.ControlPanelGraphs.renderExceptionsLineGraph = function ()
{
    new Chart(document.getElementById("admintoolsExceptionsLineChart"), {
        type:    "line",
        data:    {
            labels:   admintools.ControlPanelGraphs.graph.lineLabels,
            datasets: [
                {
                    data:        admintools.ControlPanelGraphs.graph.exceptPoints,
                    fill:        false,
                    borderColor: "rgb(75, 192, 192)",
                    tension:     0.1
                }
            ]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales:  {
                y: {
                    ticks: {
                        beginAtZero: true
                    }
                }
            }
        }
    });
};

jQuery(document).ready(function ($)
{
    // Load graphs
    admintools.ControlPanelGraphs.loadGraphs();

    // $("#admintools_graph_datepicker").datepicker({
    //     dateFormat: "yy-mm-dd"
    // });

    // Assign click handlers
    jQuery("#admintools_graph_reload").click(admintools.ControlPanelGraphs.loadGraphs);
});
