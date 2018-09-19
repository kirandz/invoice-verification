@extends('layouts.app')

@section('page_heading', 'Invoices')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#"></a>Manage Database</li>
        <li class="active">Invoices</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Invoice Data</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="invoice-table" class="table table-striped table-bordered" style="width:100%" hidden>
                            <thead>
                            <tr>
                                <th>Invoice Number</th>
                                <th>From Period</th>
                                <th>To Period</th>
                                <th>Charge Type</th>
                                <th>Flight Type</th>
                                <th>Imported Time</th>
                                <th style="text-align: center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <input type="hidden" class="id" value="{{$invoice->id}}">
                                    <td class="col-update invoice-num">{{$invoice->invoice_num}}</td>
                                    <td class="col-update" width="15%">
                                        <input type="hidden" class="datepicker form-control" id="from-period" value="{{$invoice->from_period}}">
                                        <span class="txt-view from-period">{{$invoice->from_period}}</span>
                                    </td>
                                    <td class="col-update" width="15%">
                                        <input type="hidden" class="datepicker form-control" id="to-period" value="{{$invoice->to_period}}">
                                        <span class="txt-view to-period">{{$invoice->to_period}}</span>
                                    </td>
                                    <td class="col-update charge-type">{{$invoice->charge_type}}</td>
                                    <td class="col-update flight-type">{{$invoice->flight_type}}</td>
                                    <td width="15%">{{$invoice->created_at}}
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

    <script>
        $(document).ready(function() {
            $('.datepicker').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd'
            });

            var table = $('#invoice-table');
            table.DataTable( {
                "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                "columnDefs": [
                    { "orderable": false, "targets": 6 }
                ]
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
                var invoice_num = tr.find('.invoice-num').text();
                var from_period = tr.find('#from-period').val();
                var to_period = tr.find('#to-period').val();
                var charge_type = tr.find('.charge-type').text();
                var flight_type = tr.find('.flight-type').text();

                if(invoice_num === "" || from_period === "" || to_period === "" || charge_type === "" || flight_type === ""){
                    console.log('Error')
                }
                else{
                    $.ajax({
                        url : '{{url('update-invoice')}}',
                        type : 'POST',
                        data : {
                            'id' : id,
                            'invoice_num' : invoice_num,
                            'from_period' : from_period,
                            'to_period' : to_period,
                            'charge_type' : charge_type,
                            'flight_type' : flight_type,
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
                        url : '{{url('delete-invoice')}}',
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