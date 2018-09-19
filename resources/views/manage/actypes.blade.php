@extends('layouts.app')

@section('page_heading', 'AC Types')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#"></a>Manage Database</li>
        <li class="active">AC Types</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">AC Type Data</h3>

                        <div class="box-tools pull-right">
                            <button class="btn btn-link" data-toggle="modal" data-target="#actype-modal"><i class="fa fa-plus"></i> New AC Type</button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="actype-table" class="table table-striped table-bordered" style="width:100%" hidden>
                            <thead>
                            <tr>
                                <th>AIRNAV Type</th>
                                <th>GA Type</th>
                                <th>AC Type</th>
                                <th style="text-align: center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($actypes as $actype)
                                <tr>
                                    <input type="hidden" class="id" value="{{$actype->id}}">
                                    <td class="col-update ac-type">{{$actype->ac_type}}</td>
                                    <td class="col-update ga-type">{{$actype->ga_type}}</td>
                                    <td class="col-update type">{{$actype->conv_type}}</td>
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

    <div class="modal fade" id="actype-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">New AC Type</h4>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" hidden></div>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="modal-ac" class="col-md-3 control-label">AIRNAV Type</label>

                            <div class="col-md-8">
                                <input type="text" class="form-control" id="modal-ac" placeholder="AC Type">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="modal-ga" class="col-md-3 control-label">GA Type</label>

                            <div class="col-md-8">
                                <input type="email" class="form-control" id="modal-ga" placeholder="GA Type">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="modal-conv" class="col-md-3 control-label">AC Type</label>

                            <div class="col-md-8">
                                <input type="text" class="form-control" id="modal-conv" placeholder="Type">
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
            var table = $('#actype-table');

            table.DataTable( {
                "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                "columnDefs": [
                    { "orderable": false, "targets": 3 }
                ]
            } );

            table.prop('hidden', false);

            $('.submit').click(function () {
                var ac_type = $('#modal-ac').val();
                var ga_type = $('#modal-ga').val();
                var type = $('#modal-conv').val();

                $.ajax({
                    url : '{{url('add-actype')}}',
                    type : 'POST',
                    data : {
                        'airnav_type' : ac_type,
                        'ga_type' : ga_type,
                        'ac_type' : type,
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
            });

            table.on('click', '.update', function () {
                var tr = $(this).closest('tr');

                tr.find('.col-update').prop('contenteditable', 'true').prop('style', 'border: 1px solid #3c8dbc');
                tr.find('.update').prop('style', 'display: none');
                tr.find('.save').prop('style', 'display: inline');
            });

            table.on('click', '.save', function () {
                var tr = $(this).closest('tr');

                var id = tr.find('.id').val();
                var ac_type = tr.find('.ac-type').text();
                var ga_type = tr.find('.ga-type').text();
                var type = tr.find('.type').text();

                if(type === ""){
                    console.log('Error')
                }
                else{
                    $.ajax({
                        url : '{{url('update-actype')}}',
                        type : 'POST',
                        data : {
                            'id' : id,
                            'ac_type' : ac_type,
                            'ga_type' : ga_type,
                            'type' : type,
                            '_token' : '{{csrf_token()}}'
                        },

                        success: function(response){
                            tr.find('.col-update').prop('contenteditable', 'false').prop('style', 'border: ');
                            tr.find('.update').prop('style', 'display: inline');
                            tr.find('.save').prop('style', 'display: none');

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
                        url : '{{url('delete-actype')}}',
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