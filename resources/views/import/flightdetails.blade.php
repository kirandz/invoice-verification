@extends('layouts.app')

@section('page_heading', 'Flight Details')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#"></a>Import</li>
        <li class="active">Invoices</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Import Form</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <?php $flag = 0;?>

                        @if ($message = Session::get('success'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                {{ Session::get('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @elseif($message = Session::get('error'))
                            <?php $flag = 1?>
                            <i id="msg-error-format" hidden>{{ Session::get('error') }}</i>
                        @endif

                        @if ($errors->first())
                            <?php $flag = 2?>
                            <i id="msg-error" hidden>{{ $errors->first() }}</i>
                        @endif

                        <form action="{{url('import-flightdetails')}}" class="form-submit form-horizontal" method="POST" enctype="multipart/form-data">
                            {{csrf_field()}}
                            <div class="period form-group">
                                <label for="from-period" class="col-md-3 control-label">Periode</label>

                                <div class="col-md-4">
                                    <input type="date" class="form-control" name="from_period">
                                </div>

                                <div class="col-md-4">
                                    <input type="date" class="form-control" name="to_period">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="file" class="col-md-3 control-label">File</label>

                                <div class="col-md-8">
                                    <input type="file" class="form-control" id="file" name="file" style="border: none">
                                    <p class="help-block col-md-5"><a href="{{ asset('images/upload-fd.png') }}" target="_blank"><i class="fa fa-external-link"></i> Excel Format</a></p>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-1">
                                    <button type="submit" class="submit-btn btn btn-primary col-md-11">
                                        <i class="glyphicon glyphicon-import"></i> Import
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->

            <div class="col-xs-6">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Graph</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="col-md-12">
                            <p>Actual Flight</p>
                            <canvas id="myChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->

            <div class="col-xs-12 import-format" hidden>
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Upload Format</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body" hidden>
                        <img src="{{ asset('images/upload-fd.png') }}" alt="Girl in a jacket" width="100%">
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->

    <script>
        var flag = <?php echo $flag;?>

        $(document).ready(function() {
            var msg = "";

            if(flag === 1) {
                msg = $('#msg-error-format').html();

                new PNotify({
                    title: 'Warning!',
                    text: msg,
                    type: 'error',
                    delay: 2000
                });
            }
            else if(flag === 2){
                msg = $('#msg-error').html();

                new PNotify({
                    title: 'Warning!',
                    text: msg,
                    type: 'error',
                    delay: 2000
                });
            }

            $('.datepicker').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd'
            });

            var data = <?php echo $charts; ?>;
            var total_flight = [];
            var date = [];

            for(var i = 0 ; i<data.length ; i++){
                var objDate = new Date(data[i].date);
                var temp = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

                date.splice(i, 0, temp[objDate.getMonth()] + ' ' + objDate.getFullYear());
                total_flight.splice(i, 0, data[i].total_flight);
            }

            var ctx = document.getElementById("myChart");
            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: date,
                    datasets: [
                        {
                            data: total_flight,
                            label: "Total Flight",
                            borderColor: "#3e95cd",
                            backgroundColor: "rgb(62, 149, 205, 0.3)"
                        }
                    ]
                },
                options: {
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); }, },
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
                                labelString: 'Total Flight'
                            },
                            ticks: {
                                min: 0
                            }
                        }]
                    }
                }
            });
        });
    </script>
@stop