@extends('layouts.app')

@section('page_heading', 'Airports')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#"></a>Manage Database</li>
        <li class="active">Airports</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Airport Data</h3>

                        <div class="box-tools pull-right">
                            <button class="btn btn-link" data-toggle="modal" data-target="#airport-modal"><i class="fa fa-plus"></i> New Airport</button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="airport-table" class="table table-striped table-bordered" style="width:100%" hidden>
                            <thead>
                            <tr>
                                <th>ICAO Code</th>
                                <th>IATA Code</th>
                                <th style="text-align: left">Airport Name</th>
                                <th>Country</th>
                                <th style="text-align: center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($airports as $airport)
                                <tr>
                                    <input type="hidden" class="id" value="{{$airport->id}}">
                                    <td class="col-update icao-code">{{$airport->icao_code}}</td>
                                    <td class="col-update iata-code">{{$airport->iata_code}}</td>
                                    <td class="col-update name">{{$airport->name}}</td>
                                    <td class="col-update country">{{$airport->country}}</td>
                                    <td style="text-align: center">
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

    <div class="modal fade" id="airport-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">New Airport</h4>
                </div>
                <div class="modal-body">

                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="modal-icao" class="col-md-3 control-label">ICAO Code</label>

                            <div class="col-md-8">
                                <input type="text" class="form-control" id="modal-icao" placeholder="ICAO Code">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="modal-iata" class="col-md-3 control-label">IATA Code</label>

                            <div class="col-md-8">
                                <input type="text" class="form-control" id="modal-iata" placeholder="IATA Code">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="modal-name" class="col-md-3 control-label">Airport Name</label>

                            <div class="col-md-8">
                                <input type="text" class="form-control" id="modal-name" placeholder="Airport Name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="modal-country" class="col-md-3 control-label">Country</label>

                            <div class="col-md-8">
                                <input type="text" class="form-control" id="modal-country" placeholder="Country">
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
            var table = $('#airport-table');

            table.DataTable( {
                "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                "columnDefs": [
                    { "orderable": false, "targets": 4 }
                ],
                fixedHeader: {
                    header: true
                }
            } );

            table.prop('hidden', false);

            $('.submit').click(function () {
                var tr = $(this).closest('tr');

                var icao_code = $('#modal-icao').val();
                var iata_code = $('#modal-iata').val();
                var airport_name = $('#modal-name').val();
                var country = $('#modal-country').val();

                $.ajax({
                    url : '{{url('add-airport')}}',
                    type : 'POST',
                    data : {
                        'icao_code' : icao_code,
                        'iata_code' : iata_code,
                        'airport_name' : airport_name,
                        'country' : country,
                        '_token' : '{{csrf_token()}}'
                    },

                    success: function(data){
                        if($.isEmptyObject(data.error)){
                            location.reload();

                            $('.close').click();

                            new PNotify({
                                title: "Created",
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
                var icao_code = tr.find('.icao-code').text();
                var iata_code = tr.find('.iata-code').text();
                var name = tr.find('.name').text();
                var country = tr.find('.country').text();

                if(icao_code === "" || iata_code === "" || name === "" || country === ""){
                    console.log('Error')
                }
                else{
                    $.ajax({
                        url : '{{url('update-airport')}}',
                        type : 'POST',
                        data : {
                            'id' : id,
                            'icao_code' : icao_code,
                            'iata_code' : iata_code,
                            'name' : name,
                            'country' : country,
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
                        url : '{{url('delete-airport')}}',
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