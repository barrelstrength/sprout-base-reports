(function($) {
  var SproutVisualizations = Garnish.Base.extend(
    {

      init: function() {
        this.chart = null;
        this.firstDate = 0;
        this.lastDate = 0;

        //setup listeners for date ranges
        $('input[name="reportDateFrom[date]"]').on('change', this.updateVisualizationDate.bind(this));
        $('input[name="reportDateTo[date]"]').on('change', this.updateVisualizationDate.bind(this));
      },

      updateVisualizationDate(event) {
        event.preventDefault();
        var fromDate = $('input[name="reportDateFrom[date]"]').val();
        var toDate = $('input[name="reportDateTo[date]"]').val();

        fromDate = fromDate != '' ? fromDate : this.firstDate;
        toDate = toDate != '' ? toDate : this.lastDate;

        this.chart.zoomX(
          new Date(fromDate).getTime(),
          new Date(toDate).getTime()
        );

        return false;

      },

      createChart(chartSelector, settings) {
        this.chart = new ApexCharts(document.querySelector(chartSelector), settings);
        this.chart.render();
      },

      createLineChart(title, labels, dataSeries, options, chartSelector) {
        var settings = {
          series: dataSeries,
          chart: {
            height: 500,
            type: 'line',
            zoom: {
              enabled: false
            }
          },
          dataLabels: {
            enabled: false
          },
          stroke: {
            curve: 'straight'
          },
          grid: {
            row: {
              colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
              opacity: 0.5
            },
          },
          xaxis: {
            categories: labels
          }
        };

        jQuery.extend(settings, options);
        this.createChart(chartSelector, settings);
      },

      createBarChart(title, labels, dataSeries, options, chartSelector) {
        var settings = {
          series: dataSeries,
          chart: {
            height: 500,
            type: 'bar',
            zoom: {
              enabled: false
            }
          },
          plotOptions: {
            bar: {
              horizontal: true,
            }
          },
          dataLabels: {
            enabled: false
          },
          stroke: {
            curve: 'straight'
          },
          grid: {
            row: {
              colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
              opacity: 0.5
            },
          },
          xaxis: {
            categories: labels
          }
        };
        jQuery.extend(settings, options);
        this.createChart(chartSelector, settings);
      },

      createPieChart(title, labels, dataSeries, options, chartSelector) {
        var settings = {
          series: dataSeries[0].data,
          chart: {
            width: 500,
            type: 'pie',
          },
          labels: labels,
          responsive: [
            {
              breakpoint: 480,
              options: {
                chart: {
                  width: 200
                },
                legend: {
                  position: 'bottom'
                }
              }
            }
          ]
        };
        jQuery.extend(settings, options);
        this.createChart(chartSelector, settings);
      },

      createTimeChart(title, labels, dataSeries, options, chartSelector) {
        var settings = {
          series: dataSeries,
          chart: {
            height: 500,
            type: 'line',
            zoom: {
              enabled: true
            },
            toolbar: {
              show: true,
              offsetX: 0,
              offsetY: 0,
              tools: {
                download: false,
                selection: true,
                zoom: true,
                zoomin: true,
                zoomout: true,
                pan: true,
                reset: true,
                customIcons: []
              },
              autoSelected: 'zoom'
            }
          },
          dataLabels: {
            enabled: false
          },
          stroke: {
            curve: 'straight'
          },
          grid: {
            row: {
              colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
              opacity: 0.5
            },
          },
          xaxis: {
            type: 'datetime'
          }
        };
        jQuery.extend(settings, options);
        this.createChart(chartSelector, settings);
      },

      setFirstDate(value) {
        this.firstDate = value;
      },

      setLastDate(value) {
        this.lastDate = value;
      }

    });

  Garnish.$doc.ready(function() {
    Craft.SproutVisualizations = new SproutVisualizations();
  });
})(jQuery);
