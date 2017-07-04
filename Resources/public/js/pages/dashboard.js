/* ------------------------------------------------------------------------------
 *
 *  # Dashboard configuration
 *
 *  Demo dashboard configuration. Contains charts and plugin inits
 *
 *  Version: 1.0
 *  Latest update: Aug 1, 2015
 *
 * ---------------------------------------------------------------------------- */

$(function() {

    // Initialize with options
 /*   var menu = $('.analytics-daterange').daterangepicker(
        {
            startDate: moment().subtract(29, 'days'),
            endDate: moment(),
            minDate: '01/01/2014',
            maxDate: moment(),
            dateLimit: { days: 60 },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            opens: 'left',
            applyClass: 'btn-small bg-slate',
            cancelClass: 'btn-small btn-default'
        },
        function(start, end) {
            $('.analytics-daterange span').html(start.format('MMMM D, YYYY') + ' &nbsp; - &nbsp; ' + end.format('MMMM D, YYYY'));
        }
    );

    // Display date format
    $('.analytics-daterange span').html(moment().subtract(7, 'days').format('MMMM D, YYYY') + ' &nbsp; - &nbsp; ' + moment().format('MMMM D, YYYY'));

    $('input[name="datefilter"]').on('apply.daterangepicker', function(ev, picker) {

            alert(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('.analytics-daterange').on('apply.daterangepicker', function (ev, picker) {
            var start_dt = $(document).find('input[name="daterangepicker_start"]').val();
            var end_dt = $(document).find('input[name="daterangepicker_end"]').val();
            var DofS = "<input name='beginDate' type='hidden'  value='" + start_dt + "'/>";
            var DofE = "<input name='endDate' type='hidden' value='" + end_dt + "'/>";
            $("#dashboardFilterDate").append(DofS + DofE);
            $("#dashboardFilterDate").submit();
        });*/
});
