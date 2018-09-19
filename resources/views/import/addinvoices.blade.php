@extends('layouts.app')

@section('page_heading', 'Additional Invoices')

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
                            <?php $flag = 3?>
                            <i id="msg-error-format" hidden>{{ Session::get('error') }}</i>
                        @endif

                        @if ($errors->first())
                            <?php $flag = 2?>
                            <i id="msg-error" hidden>{{ $errors->first() }}</i>
                        @endif

                        <form action="{{url('import-addinvoices')}}" class="form-submit form-horizontal" method="POST" enctype="multipart/form-data">
                            {{csrf_field()}}
                            <input type="hidden" id="flight-type" name="flight_type">

                            <div class="form-group">
                                <label for="invoice-num-prev" class="col-md-3 control-label">Invoice No (Previous)</label>

                                <div class="col-md-8" style="margin-top: 7px">
                                    <select name="unbilled_inv_id" id="invoice-num-prev" class="form-control" required="" disabled style="width: 100%">
                                        <option value=""></option>
                                        @foreach($invoices as $invoice)
                                            @if(old('unbilled_inv_id') == $invoice->id)
                                                <option value="{{$invoice->id}}" selected>{{$invoice->invoice_num}}</option>
                                            @else
                                                <option value="{{$invoice->id}}">{{$invoice->invoice_num}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @if(old('ex_rate') == '')
                                <div class="ex-rate form-group" hidden>
                                    <label for="ex-rate" class="col-md-3 control-label">Exchange Rate</label>

                                    <div class="col-md-8">
                                        <input type="number" class="form-control" id="ex-rate" name="ex_rate" value="{{ old('ex_rate') }}" placeholder="Exhange Rate">
                                    </div>
                                </div>
                            @else
                                <div class="ex-rate form-group">
                                    <label for="ex-rate" class="col-md-3 control-label">Exchange Rate</label>

                                    <div class="col-md-8">
                                        <input type="number" class="form-control" id="ex-rate" name="ex_rate" value="{{ old('ex_rate') }}" placeholder="Exhange Rate">
                                    </div>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="invoice-num" class="col-md-3 control-label">Invoice No</label>

                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="invoice-num" name="invoice_num" value="{{ old('invoice_num') }}" required="" placeholder="Invoice Number">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="file" class="col-md-3 control-label">File</label>

                                <div class="col-md-8">
                                    <input type="file" class="form-control" id="file" name="file" style="border: none" required="">
                                    <p class="help-block col-md-5"><a href="{{ asset('images/upload-inv.png') }}" target="_blank"><i class="fa fa-external-link"></i> Excel Format</a></p>
                                </div>
                            </div>

                            <input type="hidden" name="from_period" id="m-from-period">
                            <input type="hidden" name="to_period" id="m-to-period">

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

            var invoice_num_prev = $('#invoice-num-prev');

            invoice_num_prev.select2({
                placeholder: "-Choose Invoice Number-"
            });
            invoice_num_prev.prop('disabled', false);

            $('#period').change(function () {
                var id = $(this).val();

                $.ajax({
                    url : '{{url('date-period')}}',
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

            invoice_num_prev.change(function () {
                var id = $(this).val();

                $.ajax({
                    url : '{{url('find-flight-type')}}',
                    type : 'GET',
                    data : {
                        'id' : id
                    },

                    success: function(data){
                        $('#flight-type').val(data.flight_type);

                        if(data.flight_type === 'INT'){
                            $('.ex-rate').prop('hidden', false);
                            $('#ex-rate').val('10000');
                        }
                        else{
                            $('.ex-rate').prop('hidden', true);
                            $('#ex-rate').val('');
                        }
                    }
                });
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