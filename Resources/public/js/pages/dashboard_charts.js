/* ------------------------------------------------------------------------------
 *
 *  # Echarts - lines and areas
 *
 *  Lines and areas chart configurations
 *
 *  Version: 1.0
 *  Latest update: August 1, 2015
 *
 * ---------------------------------------------------------------------------- */

$(function() {


    // Set paths
    // ------------------------------

    require.config({
        paths: {
            echarts: '/js/plugins/visualization/echarts'
        }
    });


    // Configuration
    // ------------------------------

    require(
        [
            'echarts',
            'echarts/theme/limitless',
            'echarts/chart/line',
            'echarts/chart/bar'
        ],

        // Charts setup
        function (ec, limitless) {
            console.log(document.getElementById('visit_lines'));
            // Initialize charts
            // ------------------------------
            var visit_lines = ec.init(document.getElementById('visit_lines'), limitless);

            // Charts setup
            // ------------------------------

            //
            // Stacked lines options
            //
            visit_lines_options = {

                // Setup grid
                grid: {
                    x: 40,
                    x2:20,
                    y: 35,
                    y2: 25
                },

                // Add tooltip
                tooltip: {
                    trigger: 'axis'
                },

                // Add legend
                legend: {
                    data: ['Páginas vistas', 'Sesiones']
                },

                // Add custom colors
                color: ['#3b7abe', '#66BB6A'],

                // Enable drag recalculate
                calculable: true,

                // Hirozontal axis
                xAxis: [{
                    type: 'category',
                    boundaryGap: false,
                    data: [
                        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
                    ]
                }],

                // Vertical axis
                yAxis: [{
                    type: 'value'
                }],

                // Add series
                series: [
                    {
                        name: 'Páginas vistas',
                        type: 'line',
                        stack: 'Total',
                        data: [820, 932, 901, 934, 1290, 1330, 1320]
                    },
                    {
                        name: 'Sesiones',
                        type: 'line',
                        stack: 'Total',
                        data: [1020, 1132, 1101, 1234, 1590, 1930, 2320]
                    }
                ]
            };


            // ------------------------------
            visit_lines.setOption(visit_lines_options);

            // Resize charts
            // ------------------------------

            window.onresize = function () {
                setTimeout(function () {
                    visit_lines.resize();
                }, 200);
            }
        }
    );
});

// Initialize geo chart
/*google.load("visualization", "1", {packages:["geochart"]});
google.setOnLoadCallback(drawRegionsMap);

// Chart settings
function drawRegionsMap() {

    // Data
    var data = google.visualization.arrayToDataTable([
        ['Country', 'Popularity'],
        ['Spain', 230000],
        ['Germany', 200],
        ['United States', 300],
        ['Brazil', 400],
        ['Canada', 500],
        ['France', 600],
        ['RU', 700]
    ]);


    // Options
    var options = {
        fontName: 'Roboto',
        color: '#3b7abe',
        colorAxis: {colors: ['#bfd4ea', '#6a94c1', '#3b7abe']},
        height: 500,
        width: "100%",
        fontSize: 12,
        tooltip: {
            textStyle: {
                fontName: 'Roboto',
                fontSize: 13
            }
        }
    };

    // Draw chart
    var chart = new google.visualization.GeoChart($('#google-geo-region')[0]);
    chart.draw(data, options);
}*/
