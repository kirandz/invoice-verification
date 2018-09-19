@extends('layouts.app')

@section('page_heading', 'Hajj Flights')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#"></a>Manage Database</li>
        <li class="active">Hajj Flights</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Hajj Flight Data</h3>

                        <div class="box-tools pull-right">
                            <button class="btn btn-link" data-toggle="modal" data-target="#hajjflight-modal"><i class="fa fa-plus"></i> New Hajj Flight</button>/
                            {{--<button class="trigger-upload btn btn-link"><i class="glyphicon glyphicon-import"></i> Import Hajj Flight</button>/--}}
                            <button class="btn btn-link" data-toggle="modal" data-target="#import-hajjflight"><i class="glyphicon glyphicon-import"></i> Import Hajj Flight</button>/
                            {{--<form action="{{url('upload-hajjflight')}}" method="POST" enctype="multipart/form-data" hidden>--}}
                                {{--{{csrf_field()}}--}}
                                {{--<input type="file" class="upload" name="hajj_flight">--}}
                                {{--<button type="submit" class="upload-submit"></button>--}}
                            {{--</form>--}}
                            <button class="delete-all btn btn-link"><i class="fa fa-trash"></i> Delete All Data</button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="hajjflight-table" class="table table-striped table-bordered" style="width:100%" hidden>
                            <thead>
                            <tr>
                                <th>Flight No</th>
                                <th>Flt No</th>
                                <th>Valid from</th>
                                <th>Valid to</th>
                                <th style="text-align: center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($hajjflights as $hajjflight)
                                <tr>
                                    <input type="hidden" class="id" value="{{$hajjflight->id}}">
                                    <td class="flight-num" width="20%">{{$hajjflight->flight_num}}</td>
                                    <td class="col-update fn-number" width="20%">{{$hajjflight->fn_number}}</td>
                                    <td class="col-update" width="20%">
                                        <input type="hidden" class="datepicker form-control valid-from" id="valid-from" value="{{$hajjflight->valid_from}}">
                                        <span class="txt-view">{{$hajjflight->valid_from}}</span>
                                    </td>
                                    <td class="col-update" width="20%">
                                        <input type="hidden" class="datepicker form-control valid-to" id="valid-to" value="{{$hajjflight->valid_to}}">
                                        <span class="txt-view">{{$hajjflight->valid_to}}</span>
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

    <div class="modal fade" id="hajjflight-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">New Hajj Flight</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" hidden></div>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="modal-fn" class="col-md-3 control-label">Flight Number</label>

                            <div class="col-md-7">
                                <input type="text" class="form-control" id="modal-fn" placeholder="Flight Number">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="modal-vf" class="col-md-3 control-label">Valid From</label>

                            <div class="col-md-7">
                                <input type="date" class="form-control" id="modal-vf" placeholder="Flight Number">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="modal-vt" class="col-md-3 control-label">Valid From</label>

                            <div class="col-md-7">
                                <input type="date" class="form-control" id="modal-vt" placeholder="Flight Number">
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

    <div class="modal fade" id="import-hajjflight" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{url('upload-hajjflight')}}" method="POST" enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Import Hajj Flight</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger" hidden></div>
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label for="import-vf" class="col-md-3 control-label">Valid from</label>

                                <div class="col-md-7">
                                    <input type="date" class="form-control" id="import-vf" name="valid_from" placeholder="Valid from">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="import-vt" class="col-md-3 control-label">Valid to</label>

                                <div class="col-md-7">
                                    <input type="date" class="form-control" id="import-vt" name="valid_to" placeholder="Valid to">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="import-file" class="col-md-3 control-label">File</label>

                                <div class="col-md-7">
                                    <input type="file" class="form-control" id="import-file" name="file">
                                    <p class="help-block col-md-5" style="margin-bottom: 0px"><a href="{{ asset('images/upload-hajjflight.png') }}" target="_blank"><i class="fa fa-external-link"></i> Excel Format</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $flag = 0;?>

    @if ($message = Session::get('success'))
        <?php $flag = 1?>
        <i id="msg" hidden>{{ Session::get('success') }}</i>
    @elseif($errors->first())
        <?php $flag = 2?>
        <i id="msg-error" hidden>{{ $errors->first() }}</i>
    @endif

    <script>
        $('.datepicker').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd'
        });

        var flag = <?php echo $flag?>

        $(document).ready(function() {
            if(flag === 1) {
                var msg = $('#msg').html();

                new PNotify({
                    title: 'Success!',
                    text: msg,
                    type: 'success',
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

            var table = $('#hajjflight-table');

            table.DataTable( {
                "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                "columnDefs": [
                    { "orderable": false, "targets": 2 }
                ]
            } );

            table.prop('hidden', false);

            $('.submit').click(function () {
                var flight_num = $('#modal-fn').val();
                var valid_from = $('#modal-vf').val();
                var valid_to = $('#modal-vt').val();

                $.ajax({
                    url : '{{url('add-hajjflight')}}',
                    type : 'POST',
                    data : {
                        'flight_num' : flight_num,
                        'valid_from' : valid_from,
                        'valid_to' : valid_to,
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
            });

            table.on('click', '.save', function () {
                var tr = $(this).closest('tr');

                var id = tr.find('.id').val();
                var flight_num = tr.find('.flight-num').text();
                var fn_number = tr.find('.fn-number').text();
                var valid_from = tr.find('.valid-from').val();
                var valid_to = tr.find('.valid-to').val();

                if(fn_number === "" || valid_from === "" || valid_to === ""){
                    console.log('Error')
                }
                else{
                    $.ajax({
                        url : '{{url('update-hajjflight')}}',
                        type : 'POST',
                        data : {
                            'id' : id,
                            'fn_number' : fn_number,
                            'valid_from' : valid_from,
                            'valid_to' : valid_to,
                            '_token' : '{{csrf_token()}}'
                        },

                        success: function(response){
                            tr.find('.col-update').prop('contenteditable', 'false').prop('style', 'border: ');
                            tr.find('.update').prop('style', 'display: inline');
                            tr.find('.save').prop('style', 'display: none');
                            tr.find('.datepicker').prop('type', 'hidden');
                            tr.find('.txt-view').prop('hidden', false);

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
                        url : '{{url('delete-hajjflight')}}',
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

//            $('.trigger-upload').click(function () {
//                var upload = $('.upload');
//                upload.click();
//
//                upload.change(function () {
//                    $('.upload-submit').click();
//                });
//            });

            $('.delete-all').click(function () {
                (new PNotify({
                    title: 'Confirmation Needed',
                    text: 'Are you sure want to delete all data?',
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
                        url : '{{url('delete-all-hajjflight')}}',
                        type : 'POST',
                        data : {
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