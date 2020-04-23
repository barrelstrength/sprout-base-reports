(function($) {
  /** global: Craft */
  /** global: Garnish */
  var SproutVisualizations = Garnish.Base.extend(
      {
        init: function() {
        },

        createChart(chartSelector, options) {
          var chart = new ApexCharts(document.querySelector(chartSelector), options);
          chart.render();
        },

        createLineChart(labels, dataSeries, chartSelector) {
          console.log(labels);
          console.log(dataSeries);
          console.log(chartSelector);

          var options = {
            series: dataSeries,
            chart: {
              height: 350,
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
            title: {
              text: 'chart title',
              align: 'left'
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

          this.createChart(chartSelector, options);
        },

        createBarChart(labels, dataSeries, chartSelector) {
          var options = {
            series: dataSeries,
            chart: {
              height: 350,
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
            title: {
              text: 'chart title',
              align: 'left'
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

          this.createChart(chartSelector, options);
        },

        createPieChart(labels, dataSeries, chartSelector) {
          var options = {
            series: dataSeries[0].data,
            chart: {
              width: 380,
              type: 'pie',
            },
            labels: labels,
            responsive: [{
              breakpoint: 480,
              options: {
                chart: {
                  width: 200
                },
                legend: {
                  position: 'bottom'
                }
              }
            }]
          };

          var chart = new ApexCharts(document.querySelector("#chart"), options);
          chart.render();

        }
      });

  Garnish.$doc.ready(function() {
      Craft.SproutVisualizations = new SproutVisualizations();
  });
})(jQuery);
