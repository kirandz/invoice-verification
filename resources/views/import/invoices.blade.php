@extends('layouts.app')

@section('page_heading', 'Invoices')

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

                        <form action="{{url('import-invoices')}}" class="form-submit form-horizontal" method="post" enctype="multipart/form-data">
                            {{csrf_field()}}
                            <div class="form-group">
                                <label for="invoice-type" class="col-md-3 control-label">Invoice Type</label>

                                <div class="col-md-8">
                                    <select name="invoice_type" id="invoice-type" class="form-control">
                                        @if(old('invoice_type') == '')
                                            <option value="" selected disabled>-Choose Type-</option>
                                        @else
                                            <option value="" disabled>-Choose Type-</option>
                                        @endif
                                        @if(old('invoice_type') == 'Route Charge')
                                            <option value="Route Charge" selected>Route Charge</option>
                                        @else
                                            <option value="Route Charge">Route Charge</option>
                                        @endif
                                        @if(old('invoice_type') == 'TNC')
                                            <option value="TNC" selected>Terminal Navigation Charge</option>
                                        @else
                                            <option value="TNC">Terminal Navigation Charge</option>
                                        @endif
                                        @if(old('invoice_type') == 'Overflying')
                                            <option value="Overflying" selected>Overflying</option>
                                        @else
                                            <option value="Overflying">Overflying</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="flight-type" class="col-md-3 control-label">Flight Type</label>

                                <div class="col-md-8">
                                    @if(old('invoice_type') == '')
                                        <select name="flight_type" id="flight-type" class="form-control" disabled>
                                            @if(old('flight_type') == '')
                                                <option value="" selected disabled>-Choose Type-</option>
                                            @else
                                                <option value="" disabled>-Choose Type-</option>
                                            @endif
                                            @if(old('flight_type') == 'DOM')
                                                <option value="DOM" selected>Domestic</option>
                                            @else
                                                <option value="DOM">Domestic</option>
                                            @endif
                                            @if(old('flight_type') == 'INT')
                                                <option value="INT" selected>International</option>
                                            @else
                                                <option value="INT">International</option>
                                            @endif
                                        </select>
                                    @else
                                        <select name="flight_type" id="flight-type" class="form-control">
                                            @if(old('flight_type') == '')
                                                <option value="" selected disabled>-Choose Type-</option>
                                            @else
                                                <option value="" disabled>-Choose Type-</option>
                                            @endif
                                            @if(old('flight_type') == 'DOM')
                                                <option value="DOM" selected>Domestic</option>
                                            @else
                                                <option value="DOM">Domestic</option>
                                            @endif
                                            @if(old('flight_type') == 'INT')
                                                <option value="INT" selected>International</option>
                                            @else
                                                <option value="INT">International</option>
                                            @endif
                                        </select>
                                    @endif
                                </div>
                            </div>

                            @if(old('ex_rate') == '')
                                <div class="ex-rate form-group" hidden>
                                    <label for="ex-rate" class="col-md-3 control-label">Exchange Rate</label>

                                    <div class="col-md-8">
                                        <input type="number" class="form-control" id="ex-rate" name="ex_rate" value="{{ old('ex_rate') }}">
                                    </div>
                                </div>
                            @else
                                <div class="ex-rate form-group">
                                    <label for="ex-rate" class="col-md-3 control-label">Exchange Rate</label>

                                    <div class="col-md-8">
                                        <input type="number" class="form-control" id="ex-rate" name="ex_rate" value="{{ old('ex_rate') }}">
                                    </div>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="invoice-num" class="col-md-3 control-label">Invoice No</label>

                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="invoice-num" name="invoice_num" placeholder="Invoice Number" value="{{ old('invoice_num') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="period" class="col-md-3 control-label">Periode</label>

                                <div class="col-md-8">
                                    <select name="detail_id" id="period" class="form-control" disabled style="width: 100%">
                                        <option value=""></option>
                                        @foreach($flights as $flight)
                                            @if(old('detail_id') == $flight->id)
                                                <?php
                                                $from_period = date("d M Y", strtotime($flight->from_period));
                                                $to_period = date("d M Y", strtotime($flight->to_period));
                                                ?>
                                                <option value="{{$flight->id}}" selected>{{$from_period}} - {{$to_period}}</option>
                                            @else
                                                <?php
                                                $from_period = date("d M Y", strtotime($flight->from_period));
                                                $to_period = date("d M Y", strtotime($flight->to_period));
                                                ?>
                                                <option value="{{$flight->id}}">{{$from_period}} - {{$to_period}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="from_period" id="from-period" value="{{ old('from_period') }}">
                                    <input type="hidden" name="to_period" id="to-period" value="{{ old('to_period') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="file" class="col-md-3 control-label">File</label>

                                <div class="col-md-8">
                                    <input type="file" class="form-control" id="file" name="file" style="border: none">
                                    <p class="help-block col-md-5"><a href="{{ asset('images/upload-inv.png') }}" target="_blank"><i class="fa fa-external-link"></i> Excel Format</a></p>
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
                            <canvas id="myChart" width="400" height="200"></canvas>
                        </div>
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

        $(document).ready(function () {
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

            var period = $('#period');

            period.select2({
                placeholder: "-Choose Period-"
            });
            period.prop('disabled', false);

            $('#invoice-type').change(function () {
                $('#flight-type').prop('disabled', false);
            });

            period.change(function () {
                var id = $(this).val();

                $.ajax({
                    url : '{{url('flight-period')}}',
                    type : 'GET',
                    data : {
                        'id' : id
                    },

                    success: function(data){
                        $('#from-period').val(data.from_period);
                        $('#to-period').val(data.to_period);
                    }
                });
            });

            $('#flight-type').change(function () {
                if($(this).val() === 'INT'){
                    $('.ex-rate').prop('hidden', false);
                    $('#ex-rate').val('10000');
                }
                else{
                    $('.ex-rate').prop('hidden', true);
                    $('#ex-rate').val('');
                }
            });

            $('.close').click(function () {
                $('.alert').fadeOut();
            });

            $('.form-submit').submit(function () {
                $('.submit-btn').prop('disabled', true);
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
                                labelString: 'Total Flight',
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