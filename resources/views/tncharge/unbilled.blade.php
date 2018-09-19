@extends('layouts.app')

@section('page_heading', 'Terminal Navigation Charge')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#"></a>Unbilled</li>
        <li class="active">Terminal Navigation Charge</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Unbilled Flight</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="col-xs-3">
                            <div class="form-group">
                                <label>From Period:</label>

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
                                <label>To Period:</label>

                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="text" class="datepicker form-control pull-right" id="to-period" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-6 download" hidden>
                            <a class="btn btn-link pull-right" id="download" style="margin-top: 20px"><i class="fa fa-download"></i> Download File (.xlsx)</a>
                        </div>
                        <br><br><br>
                        <hr>

                        <div class="div-table" hidden>
                            <table id="table-tnc" class="table table-striped table-bordered" style="width:100%;display: block;overflow-x: auto;"></table>
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
        $(document).ready(function() {
            var datepicker = $('.datepicker');

            datepicker.prop('disabled', false);
            datepicker.datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy'
            });

            var div_table = $('.div-table');
            var from_period = $('#from-period');
            var to_period = $('#to-period');

            from_period.change(function () {
                if(to_period.val() !== ''){
                    var fp_val = from_period.val().split("-").reverse().join("-");
                    var tp_val = to_period.val().split("-").reverse().join("-");
                    var period = fp_val.toString()+'to'+tp_val.toString();

                    div_table.prop('hidden', true);

                    $('.download').prop('hidden', false);
                    $('a#download').attr('href', 'download-unbilled-tnc/'+period);

                    find_period(fp_val, tp_val, 'period');
                }
            });

            to_period.change(function () {
                if(from_period.val() !== ''){
                    var fp_val = from_period.val().split("-").reverse().join("-");
                    var tp_val = to_period.val().split("-").reverse().join("-");
                    var period = fp_val.toString()+'to'+tp_val.toString();

                    div_table.prop('hidden', true);

                    $('.download').prop('hidden', false);
                    $('a#download').attr('href', 'download-unbilled-tnc/'+period);

                    find_period(fp_val, tp_val, 'period');
                }
            });

            function find_period(from_period, to_period){
                var dataSet = [];

                $.ajax({
                    url : '{{url('unbilled-tnc-details')}}',
                    type : 'GET',
                    data : {
                        'from_period' : from_period,
                        'to_period' : to_period
                    },

                    success: function(data){
                        var dataSet = [];

                        for(var i=0;i<data.length;i++){
                            dataSet[i] = [
                                data[i].fn_number,
                                data[i].type,
                                data[i].service,
                                data[i].ac_registration,
                                data[i].dep_ap_sched,
                                data[i].dep_sched_dt,
                                data[i].dep_ap_act,
                                data[i].dep_act_dt,
                                data[i].arr_ap_sched,
                                data[i].arr_sched_dt,
                                data[i].arr_ap_act,
                                data[i].arr_act_dt,
                                data[i].leg_state,
                                data[i].remarks
                            ];
                        }

                        $('#table-tnc').DataTable( {
                            "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                            "destroy": true,
                            data: dataSet,
                            columns: [
                                { title: "Flight No" },
                                { title: "Type" },
                                { title: "Service" },
                                { title: "AC Reg" },
                                { title: "Dept Sched" },
                                { title: "Dept Sched Date" },
                                { title: "Dept Act" },
                                { title: "Dept Act Date" },
                                { title: "Arr Sched" },
                                { title: "Arr Sched Date" },
                                { title: "Arr Act" },
                                { title: "Arr Act Date" },
                                { title: "Leg State" },
                                { title: "Remarks" }
                            ]
                        } );

                        div_table.prop('hidden', false);
                    }
                });
            }
        });
    </script>
@stop