(function ($, API, global, undefined) {

  var gapi, defaultOpts = {},
      SPARKLINE_DEFAULT_HEIGHT = 40,
      SPARKLINE_DEFAULT_WIDTH = 100;

  // Load the visualizations API and store the promise object so that it can
  // be used to safely invoke the APIs functions
  gapi = global.CDT.gapi.load('visualization', '1.0', {
    packages: [ 'corechart' ]
  });

  // Create LineChart default options
  defaultOpts.lineChart = {
    backgroundColor: 'transparent'
  };

  // Create LineChart options to create a sparkline
  defaultOpts.sparkline = {
    axisTitlesPosition: 'none',
    chartArea: {
      height: SPARKLINE_DEFAULT_HEIGHT,
      width: SPARKLINE_DEFAULT_WIDTH
    },
    enableInteractivity: false,
    hAxis: {
      baselineColor: 'transparent',
      gridlines: { color: 'transparent' },
      textPosition: 'none'
    },
    height: SPARKLINE_DEFAULT_HEIGHT,
    legend: { position: 'none' },
    titlePosition: 'none',
    vAxis: {
      baselineColor: 'transparent',
      gridlines: { color: 'transparent' },
      textPosition: 'none'
    },
    width: SPARKLINE_DEFAULT_WIDTH
  };

  // Function that creates a line chart
  API.lineChart = function (sel, data, opts) {
    var dfd = $.Deferred();

    gapi.done(function () {
      var ctx = $(sel),
          dataTbl = $.isArray(data)
            ? global.google.visualization.arrayToDataTable(data)
            : new global.google.visualization.DataTable(data),
          chart = new global.google.visualization.LineChart(ctx[0]);

      chart.draw(dataTbl, $.extend({}, defaultOpts.lineChart, opts));

      // Now that the API has been loaded and the chart has been drawn resolve
      // any callbacks that would like to make use of the chart instance.
      dfd.resolveWith(ctx, [ chart ]);
    });

    return dfd.promise();
  }

  // Create a function that creates a sparkline line chart
  API.sparkline = function (sel, data, opts) {
    return API.lineChart(sel, data, $.extend({}, defaultOpts.sparkline, opts));
  };

} (jQuery, CDT.ns('CDT.visualizations'), window));
