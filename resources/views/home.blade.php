@extends('layouts.app')

@section('page_heading', 'Dashboard')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
    </ol>
@endsection

@section('section')
    <!-- Main content -->
    <section class="content">
        <!-- Small boxes (Stat box) -->

        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="glyphicon glyphicon-road"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Route Charge</span>
                        <span class="info-box-number rc-box"></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>

            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-location-arrow"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Terminal Navigation Charge</span>
                        <span class="info-box-number tnc-box"></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
        </div>
        <!-- /.row -->
        <!-- Main row -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-dashboard"></i> Charge</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-3">
                                <div class="form-group">
                                    <label for="from-period">From Period:</label>

                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="datepicker form-control pull-right" id="from-period" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xs-3">
                                <div class="form-group">
                                    <label for="to-period">To Period:</label>

                                    <div class="input-group date">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                        <input type="text" class="datepicker form-control pull-right" id="to-period" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="col-md-12 content-dashboard" hidden>
                            <canvas id="barChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
            <div class="col-xs-6 flight">
                <div class="box box-success" id="pie-rc" hidden>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-plane"></i> Flight - Route Charge</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="col-md-12">
                            <div class="form-group col-md-5">
                                <select name=""  id="group-by-rc" class="form-control" style="width: 100%">
                                    <option value="" selected disabled>-Select Group By-</option>
                                    <option value="type">Type</option>
                                    <option value="service">Service</option>
                                </select>
                            </div>

                            <div class="div-pie-rc">
                                <canvas id="pieChart-rc" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
            <div class="col-xs-6 flight">
                <div class="box box-danger" id="pie-tnc" hidden>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-plane"></i> Flight - Terminal Navigation Charge</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="col-md-12">
                            <div class="form-group col-md-5">
                                <select name="" id="group-by-tnc" class="form-control" style="width: 100%">
                                    <option value="" selected disabled>-Select Group By-</option>
                                    <option value="type">Type</option>
                                    <option value="service">Service</option>
                                </select>
                            </div>


                            <div class="div-pie-tnc">
                                <canvas id="pieChart-tnc" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row (main row) -->

    </section>
    <!-- /.content -->
    <input type="hidden" id="month">

    <script>
        $(document).ready(function() {
            find_period('', '', 'all');

            var datepicker = $('.datepicker');

            datepicker.datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy'
            });

            datepicker.prop('disabled', false);

            var div_content = $('.content-dashboard');
            var div_flight = $('.flight');
            var from_period = $('#from-period');
            var to_period = $('#to-period');
            var fp_val;
            var tp_val;

            var barChart;

            from_period.change(function () {
                if(to_period.val() !== ''){
                    div_content.prop('hidden', true);
                    div_flight.prop('hidden', true);
                    barChart.destroy();

                    fp_val = from_period.val().split("-").reverse().join("-");
                    tp_val = to_period.val().split("-").reverse().join("-");

                    find_period(fp_val, tp_val, 'period');
                }
            });

            to_period.change(function () {
                if(from_period.val() !== ''){
                    div_content.prop('hidden', true);
                    div_flight.prop('hidden', true);
                    barChart.destroy();

                    fp_val = from_period.val().split("-").reverse().join("-");
                    tp_val = to_period.val().split("-").reverse().join("-");

                    find_period(fp_val, tp_val, 'period');
                }
            });

            function find_period(from_period, to_period, flag) {
                $.ajax({
                    url : '{{url('dashboard')}}',
                    type : 'get',
                    data : {
                        'from_period' : from_period,
                        'to_period' : to_period,
                        'flag' : flag
                    },

                    success: function(data){
                        var label = [];
                        var data_rc = [];
                        var data_tnc = [];
                        var total_rc = 0;
                        var total_tnc = 0;
                        var month = $('#month');

                        if(data.length === 0){
                            $('.rc-box').html('Rp 0');
                            $('.tnc-box').html('Rp 0');

                            new PNotify({
                                title: "Warning!",
                                text: 'Data not found!',
                                type: 'error',
                                delay: 2000
                            });
                        }
                        else{
                            div_content.prop('hidden', false);

                            for(var i=0 ; i<data.length ; i++){
                                var objDate = new Date(data[i].date);
                                var temp = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

                                label.splice(i, 0, temp[objDate.getMonth()] + ' ' + objDate.getFullYear());
                                data_rc.splice(i, 0, data[i].total_rc);
                                data_tnc.splice(i, 0, data[i].total_tnc);

                                total_rc += data[i].total_rc;
                                total_tnc += data[i].total_tnc;
                            }

                            $('.rc-box').html('Rp '+total_rc.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                            $('.tnc-box').html('Rp '+total_tnc.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));

//                            Chart
                            var x = $('#barChart');
                            barChart = new Chart(x, {
                                type: 'bar',
                                data: {
                                    labels: label,
                                    datasets: [
                                        {
                                            label: 'Route Charge',
                                            data: data_rc,
                                            backgroundColor: '#00a65a'

                                        },
                                        {
                                            label: 'Terminal Navigation Charge',
                                            data: data_tnc,
                                            backgroundColor: '#dd4b39'

                                        }
                                    ]
                                },
                                options: {
                                    tooltips: {
                                        callbacks: {
                                            label: function(tooltipItem, data) {
                                                return tooltipItem.yLabel.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                            }
                                        }
                                    },
                                    onClick: function(c,i) {
                                        e = i[0];
                                        var x_value = this.data.labels[e._index];
                                        var y_value = this.data.datasets[0].data[e._index];
                                        chart_rc(x_value, 'type');
                                        chart_tnc(x_value, 'type');
                                        month.val(x_value);

                                        div_flight.prop('hidden', false);
                                    },
                                    scales: {
                                        xAxes: [{
                                            scaleLabel: {
                                                display: true,
                                                labelString: 'Month'
                                            }
                                        }],
                                        yAxes: [{
                                            scaleLabel: {
                                                display: true,
                                                labelString: 'Total Charge (Rp)'
                                            },
                                            ticks: {
                                                min: 0
                                            }
                                        }]
                                    }
                                }
                            });

                            $('#group-by-rc').change(function () {
                                var group_by = $(this).val();

                                chart_rc(month.val(), group_by);
                            });

                            $('#group-by-tnc').change(function () {
                                var group_by = $(this).val();

                                chart_tnc(month.val(), group_by);
                            });
                        }
                    }
                });
            }

            function chart_rc(date, group_by){
                var months = {
                    'January' : '1',
                    'February' : '2',
                    'March' : '3',
                    'April' : '4',
                    'May' : '5',
                    'June' : '6',
                    'July' : '7',
                    'August' : '8',
                    'September' : '9',
                    'October' : '10',
                    'November' : '11',
                    'December' : '12'
                };

                var month = months[date.slice(0, -5)];
                var year = date.slice(-4);
                var data_chart = [];
                var label = [];
                var div_pie_rc = $('.div-pie-rc');
                var pie_rc = $('#pieChart-rc');

                $.ajax({
                    url: '{{url('dashboard-pie')}}',
                    type: 'get',
                    data : {
                        'month' : month,
                        'year' : year,
                        'charge_type' : 'Route Charge',
                        'group_by' : group_by
                    },

                    success: function (data) {
                        if(data.length === 0) {
                            pie_rc.remove();

                            new PNotify({
                                title: "Warning!",
                                text: 'Route Charge data not found!',
                                type: 'error',
                                delay: 2000
                            });
                        }
                        else{
                            if(group_by === 'service'){
                                for(var i=0 ; i<data.length ; i++){
                                    label.splice(i, 0, data[i].service);
                                    data_chart.splice(i, 0, data[i].total_flight);
                                }
                            }
                            else{
                                for(var i=0 ; i<data.length ; i++){
                                    label.splice(i, 0, data[i].type);
                                    data_chart.splice(i, 0, data[i].total_flight);
                                }
                            }
                            $('#pie-rc').prop('hidden', false);

                            pie_rc.remove();

                            div_pie_rc.append('<canvas id="pieChart-rc" width="400" height="200"></canvas>');
                            var pieChart_rc = new Chart($('#pieChart-rc'), {
                                type: 'doughnut',
                                data: {
                                    labels: label,
                                    datasets: [
                                        {
                                            data: data_chart,
                                            backgroundColor: [
                                                '#dd4b39',
                                                '#00a65a',
                                                'rgb(210, 214, 222)',
                                                'rgb(60, 141, 188)',
                                                'rgb(0, 192, 239)',
                                                'rgb(243, 156, 18)',
                                                '#dd4b39',
                                                '#00a65a',
                                                'rgb(210, 214, 222)',
                                                'rgb(60, 141, 188)',
                                                'rgb(0, 192, 239)',
                                                'rgb(243, 156, 18)'
                                            ]
                                        }]
                                }
                            });
                        }
                    }
                });
            }

            function chart_tnc(date, group_by){
                var months = {
                    'January' : '1',
                    'February' : '2',
                    'March' : '3',
                    'April' : '4',
                    'May' : '5',
                    'June' : '6',
                    'July' : '7',
                    'August' : '8',
                    'September' : '9',
                    'October' : '10',
                    'November' : '11',
                    'December' : '12'
                };

                var month = months[date.slice(0, -5)];
                var year = date.slice(-4);
                var data_chart = [];
                var label = [];
                var div_pie_tnc = $('.div-pie-tnc');
                var pie_tnc = $('#pieChart-tnc');

                $.ajax({
                    url: '{{url('dashboard-pie')}}',
                    type: 'get',
                    data : {
                        'month' : month,
                        'year' : year,
                        'charge_type' : 'TNC',
                        'group_by' : group_by
                    },

                    success: function (data) {
                        if(data.length === 0) {
                            pie_tnc.remove();

                            new PNotify({
                                title: "Warning!",
                                text: 'Terminal Navigation Charge data not found!',
                                type: 'error',
                                delay: 2000
                            });
                        }
                        else{
                            if(group_by === 'service'){
                                for(var i=0 ; i<data.length ; i++){
                                    label.splice(i, 0, data[i].service);
                                    data_chart.splice(i, 0, data[i].total_flight);
                                }
                            }
                            else{
                                for(var i=0 ; i<data.length ; i++){
                                    label.splice(i, 0, data[i].type);
                                    data_chart.splice(i, 0, data[i].total_flight);
                                }
                            }
                            $('#pie-tnc').prop('hidden', false);

                            pie_tnc.remove();

                            div_pie_tnc.append('<canvas id="pieChart-tnc" width="400" height="200"></canvas>');
                            var pieChart_tnc = new Chart($('#pieChart-tnc'), {
                                type: 'doughnut',
                                data: {
                                    labels: label,
                                    datasets: [
                                        {
                                            data: data_chart,
                                            backgroundColor: [
                                                '#dd4b39',
                                                '#00a65a',
                                                'rgb(210, 214, 222)',
                                                'rgb(60, 141, 188)',
                                                'rgb(0, 192, 239)',
                                                'rgb(243, 156, 18)',
                                                '#dd4b39',
                                                '#00a65a',
                                                'rgb(210, 214, 222)',
                                                'rgb(60, 141, 188)',
                                                'rgb(0, 192, 239)',
                                                'rgb(243, 156, 18)'
                                            ]
                                        }]
                                }
                            });
                        }
                    }
                });
            }
        });
    </script>
@stop