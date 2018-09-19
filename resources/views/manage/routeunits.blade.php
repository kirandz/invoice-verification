@extends('layouts.app')

@section('page_heading', 'Route Units')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#"></a>Manage Database</li>
        <li class="active">Route Units</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Route Unit Data</h3>

                        <div class="box-tools pull-right">
                            <button class="btn btn-link" data-toggle="modal" data-target="#routeunit-modal"><i class="fa fa-plus"></i> New Route Unit</button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="routeunit-table" class="table table-striped table-bordered" style="width:100%" hidden>
                            <thead>
                            <tr>
                                <th>Charge</th>
                                <th>Valid From</th>
                                <th>Valid To</th>
                                <th>Flight Type</th>
                                <th style="text-align: center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($routeunits as $routeunit)
                                <tr>
                                    <input type="hidden" class="id" value="{{$routeunit->id}}">
                                    <td class="col-update">
                                        @if($routeunit->flight_type == "INT")
                                            <span class="currency">$ </span>
                                        @else
                                            <span class="currency">Rp </span>
                                        @endif
                                        <span class="charge">{{$routeunit->charge}}</span>
                                    </td>
                                    <td class="col-update" width="25%">
                                        <input type="hidden" class="datepicker form-control" id="valid-from" value="{{$routeunit->from_date}}">
                                        <span class="txt-view valid-from">{{$routeunit->from_date}}</span>
                                    </td>
                                    <td class="col-update" width="25%">
                                        <input type="hidden" class="datepicker form-control" id="valid-to" value="{{$routeunit->to_date}}">
                                        <span class="txt-view valid-to">{{$routeunit->to_date}}</span>
                                    </td>
                                    <td class="col-update flight-type">{{$routeunit->flight_type}}
                                    </td>
                                    <td style="text-align: center" width="15%">
                                        <button class="btn btn-link update" data-toggle="tooltip" title="Update"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-link save" data-toggle="tooltip" title="Save" style="display: none"><i class="fa fa-save"></i></button>
                                        <button class="btn btn-link delete"><i class="fa fa-trash" data-toggle="tooltip" title="Delete"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
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

    <div class="modal fade" id="routeunit-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">New Route Unit</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" hidden></div>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="modal-charge" class="col-md-3 control-label">Charge</label>

                            <div class="col-md-8">
                                <input type="number" class="form-control" id="modal-charge" placeholder="Charge">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="modal-from" class="col-md-3 control-label">Valid From</label>

                            <div class="col-md-8">
                                <input type="date" class="form-control" id="modal-from">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="modal-to" class="col-md-3 control-label">Valid To</label>

                            <div class="col-md-8">
                                <input type="date" class="form-control" id="modal-to">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="modal-ft" class="col-md-3 control-label">Flight Type</label>

                            <div class="col-md-8">
                                <select name="" id="modal-ft" class="form-control">
                                    <option value="" selected disabled>-Choose Type-</option>
                                    <option value="DOM">Domestic</option>
                                    <option value="INT">International</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="submit btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.datepicker').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd'
            });

            var table = $('#routeunit-table');

            table.DataTable( {
                "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                "columnDefs": [
                    { "orderable": false, "targets": 4 }
                ]
            } );

            table.prop('hidden', false);

            $('.submit').click(function () {
                var charge = $('#modal-charge').val();
                var valid_from = $('#modal-from').val();
                var valid_to = $('#modal-to').val();
                var flight_type = $('#modal-ft').val();

                $.ajax({
                    url : '{{url('add-routeunit')}}',
                    type : 'POST',
                    data : {
                        'charge' : charge,
                        'valid_from' : valid_from,
                        'valid_to' : valid_to,
                        'flight_type' : flight_type,
                        '_token' : '{{csrf_token()}}'
                    },

                    success: function(data){
                        if($.isEmptyObject(data.error)){
                            location.reload();

                            $('.close').click();

                            new PNotify({
                                title: "Create",
                                text: data.success,
                                type: 'success',
                                delay: 2000
                            });

                        }else{
                            new PNotify({
                                title: "Error",
                                text: data.error,
                                type: 'error',
                                delay: 2000
                            });
                        }
                    }
                });

                function printErrorMsg (message) {

                }
            });

            table.on('click', '.update', function () {
                var tr = $(this).closest('tr');

                tr.find('.col-update').prop('contenteditable', 'true').prop('style', 'border: 1px solid #3c8dbc');
                tr.find('.update').prop('style', 'display: none');
                tr.find('.save').prop('style', 'display: inline');
                tr.find('.datepicker').prop('type', 'text');
                tr.find('.txt-view').prop('hidden', true);
                tr.find('.currency').prop('hidden', true);
            });

            table.on('click', '.save', function () {
                var tr = $(this).closest('tr');

                var id = tr.find('.id').val();
                var charge = tr.find('.charge').text();
                var valid_from = tr.find('#valid-from').val();
                var valid_to = tr.find('#valid-to').val();
                var flight_type = tr.find('.flight-type').text();

                console.log(charge);

                if(charge === "" || valid_from === "" || valid_to === "" || flight_type === ""){
                    console.log('Error')
                }
                else{
                    $.ajax({
                        url : '{{url('update-routeunit')}}',
                        type : 'POST',
                        data : {
                            'id' : id,
//                            'charge' : charge,
                            'valid_from' : valid_from,
                            'valid_to' : valid_to,
                            'flight_type' : flight_type,
                            '_token' : '{{csrf_token()}}'
                        },

                        success: function(response){
                            tr.find('.col-update').prop('contenteditable', 'false').prop('style', 'border: ');
                            tr.find('.update').prop('style', 'display: inline');
                            tr.find('.save').prop('style', 'display: none');
                            tr.find('.datepicker').prop('type', 'hidden');
                            tr.find('.txt-view').prop('hidden', false);
                            tr.find('.currency').prop('hidden', false);

                            tr.find('.valid-from').text(valid_from);
                            tr.find('.valid-to').text(valid_to);

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
                        url : '{{url('delete-routeunit')}}',
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
        } );

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@stop