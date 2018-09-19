@extends('layouts.app')

@section('page_heading', 'Flight Details')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#"></a>Manage Database</li>
        <li class="active">Flight Details</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Flight Data</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="flight-table" class="table table-striped table-bordered" style="width:100%" hidden>
                            <thead>
                            <tr>
                                <th>From Period</th>
                                <th>To Period</th>
                                <th>Imported Time</th>
                                <th style="text-align: center">Action</th>
                                <th style="text-align: center">Data</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($flights as $flight)
                                <tr>
                                    <input type="hidden" class="id" value="{{$flight->id}}">
                                    <td>
                                        <input type="hidden" class="datepicker form-control" id="from-period" value="{{$flight->from_period}}">
                                        <span class="txt-view from-period">{{$flight->from_period}}</span>

                                    </td>
                                    <td>
                                        <input type="hidden" class="datepicker form-control" id="to-period" value="{{$flight->to_period}}">
                                        <span class="txt-view to-period">{{$flight->to_period}}</span>
                                    </td>
                                    <td>{{$flight->created_at}}</td>
                                    <td style="text-align: center" width="15%">
                                        <button class="btn btn-link update" data-toggle="tooltip" title="Update"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-link save" data-toggle="tooltip" title="Save" style="display: none"><i class="fa fa-save"></i></button>
                                        <button class="btn btn-link delete"><i class="fa fa-trash" data-toggle="tooltip" title="Delete"></i></button>
                                    </td>
                                    <td style="text-align: center" width="10%">
                                        <button class="btn btn-link view"><i class="fa fa-table" data-toggle="tooltip" title="Table"></i></button>
                                        <a href="{{url('download-fd/'.$flight->id)}}" class="btn btn-link"><i class="fa fa-download" data-toggle="tooltip" title="Download"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>

            <div class="col-xs-12">
                <div class="box flight-details" hidden>
                    <div class="box-header with-border">
                        <h3 class="box-title">Flight Details Data</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div id="div-fd">
                            <table id="flightdetail-table" class="table table-striped table-bordered" style="width:100%;display: block;overflow-x: auto;"></table>
                        </div>
                    </div>
                    <!-- /.box-body -->

                    <!-- Loading (remove the following to stop the loading)-->
                    <div class="overlay" hidden>
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <!-- end loading -->
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
//            Flight
            $('.datepicker').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd'
            });

            var overlay = $('.overlay');
            var table = $('#flight-table');

            table.DataTable( {
                "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                "columnDefs": [
                    { "orderable": false, "targets": [3, 4] }
                ],
                fixedHeader: {
                    header: true
                }
            } );

            table.prop('hidden', false);

            table.on('click', '.update', function () {
                var tr = $(this).closest('tr');

                tr.find('.col-update').prop('contenteditable', 'true').prop('style', 'border: 1px solid #3c8dbc');
                tr.find('.update').prop('style', 'display: none');
                tr.find('.save').prop('style', 'display: inline');
                tr.find('.datepicker').prop('type', 'text');
                tr.find('.txt-view').prop('hidden', true);
            });

            table.on('click', '.save', function () {
                var tr = $(this).closest('tr');

                var id = tr.find('.id').val();
                var from_period = tr.find('#from-period').val();
                var to_period = tr.find('#to-period').val();

                if(from_period === "" || to_period === ""){
                    console.log('Error')
                }
                else{
                    $.ajax({
                        url : '{{url('update-flight')}}',
                        type : 'POST',
                        data : {
                            'id' : id,
                            'from_period' : from_period,
                            'to_period' : to_period,
                            '_token' : '{{csrf_token()}}'
                        },

                        success: function(response){
                            tr.find('.col-update').prop('contenteditable', 'false').prop('style', 'border: ');
                            tr.find('.update').prop('style', 'display: inline');
                            tr.find('.save').prop('style', 'display: none');
                            tr.find('.datepicker').prop('type', 'hidden');
                            tr.find('.txt-view').prop('hidden', false);

                            tr.find('.from-period').text(from_period);
                            tr.find('.to-period').text(to_period);

                            location.reload();

                            new PNotify({
                                title: "Update",
                                text: response.success,
                                type: 'success',
                                delay: 2000
                            });
                        }
                    })
                }
            });

            table.on('click', '.delete', function () {
                var tr = $(this).closest('tr');
                var id = tr.find('.id').val();

                (new PNotify({
                    title: 'Confirmation Needed',
                    text: 'Are you sure want to delete?',
                    icon: 'glyphicon glyphicon-question-sign',
                    type: 'error',
                    hide: false,
                    confirm: {
                        confirm: true
                    },
                    buttons: {
                        closer: false,
                        sticker: false
                    },
                    history: {
                        history: false
                    }
                })).get().on('pnotify.confirm', function() {
                    $.ajax({
                        url : '{{url('delete-flight')}}',
                        type : 'POST',
                        data : {
                            'id' : id,
                            '_method' : 'DELETE',
                            '_token' : '{{csrf_token()}}'
                        },

                        success: function(response){
                            location.reload();

                            new PNotify({
                                title: "Delete",
                                text: response.success,
                                type: 'success',
                                delay: 2000
                            });
                        }
                    });
                }).on('pnotify.cancel', function() {});
            });

//            Flight Details
            table.on('click', '.view', function () {
                $('.flight-details').prop('hidden', false);
                $('#div-fd').prop('hidden', true);
                overlay.prop('hidden', false);
                var tr = $(this).closest('tr');

                var id = tr.find('.id').val();
                var table_fd = $('#flightdetail-table');

                $.ajax({
                    url : '{{url('flight-details')}}',
                    type : 'GET',
                    data : {
                        'id' : id
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
                                data[i].remarks,
                                data[i].flight_check_rc,
                                data[i].flight_check_tnc,
                            ];
                        }

                        table_fd.DataTable( {
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
                                { title: "Remarks" },
                                { title: "Status RC" },
                                { title: "Status TNC" }
                            ]
                        } );

                        $('#div-fd').prop('hidden', false);
                        overlay.prop('hidden', true);
                    }
                });
            });
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@stop