@extends('layouts.app')

@section('page_heading', 'Terminal Navigation Charge')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#"></a>Charge</li>
        <li class="active">Terminal Navigation Charge</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Invoice Data</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="invoice-num">Invoice Number</label>
                                <select class="form-control" id="invoice-num" style="width: 100%;" disabled>
                                    <option value=""></option>
                                    <option value="all" id="inv-all">All Invoice</option>
                                    @foreach($invoices as $invoice)
                                        <option value="{{$invoice->id}}">{{$invoice->invoice_num}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-xs-8 download" hidden>
                            <a class="btn btn-link pull-right" id="download" style="margin-top: 20px" target="_blank"><i class="fa fa-download"></i> Download File (.xlsx)</a>
                        </div>

                        <br><br><br>
                        <hr>

                        <!-- Custom Tabs -->
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#sum" data-toggle="tab" class="nav-sum">Summary</a></li>
                                <li><a href="#inv" data-toggle="tab" class="nav-piv">Invoice</a></li>
                                <li><a href="#det" data-toggle="tab" class="nav-piv">Detail Flight</a></li>
                                <li><a href="#piv" data-toggle="tab" class="nav-piv">Pivot</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="sum">
                                    <div class="sum-table">
                                        <!-- Period -->
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

                                        <br><br><br><br>

                                        <div class="row">
                                            <div class="sum-table-dom col-md-10"></div>
                                            <div class="sum-table-total-dom col-md-2"></div>
                                        </div>

                                        <div class="row">
                                            <div class="sum-table-int col-md-10"></div>
                                            <div class="sum-table-total-int col-md-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="inv">
                                    <div class="inv-table">
                                        <table id="inv-table" class="table table-striped table-bordered" style="width:100%"></table>
                                    </div>
                                </div>

                                <div class="tab-pane" id="det">
                                    <div class="det-table">
                                        <table id="det-table" class="table table-striped table-bordered" style="width:100%"></table>
                                    </div>
                                </div>

                                <div class="tab-pane" id="piv">
                                    <div class="piv-table">
                                        <table id="piv-table" class="table table-striped table-bordered" style="width:100%"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- nav-tabs-custom -->

                        <div class="div-table">
                            <table id="table-tnc" class="table table-striped table-bordered" style="width:100%"></table>
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

    <div class="modal fade" id="pivot-detail" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Detail Pivot</h4>
                </div>
                <div class="modal-body">
                    <div class="piv-inv"></div>
                    <br><br>
                    <div class="piv-det"></div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" class="piv-id">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" style="margin-right: 15px">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="update-service" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update Service</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal">
                        <input type="hidden" id="m-id">
                        <div class="form-group">
                            <label for="m-service" class="control-label col-md-4">Service</label>

                            <div class="col-md-5">
                                <input type="text" class="form-control" id="m-service">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="update-service btn btn-primary" data-dismiss="modal">Update</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="update-type" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update Type</h4>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal">
                        <input type="hidden" id="m-type-old">
                        <div class="form-group">
                            <label for="m-type" class="control-label col-md-4">Type</label>

                            <div class="col-md-5">
                                <input type="text" class="form-control" id="m-type">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="update-type btn btn-primary" data-dismiss="modal">Update</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {

//            Select, Nav Tabs
            var invoice_num = $('#invoice-num');
            var datepicker = $('.datepicker');

            datepicker.datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy'
            });

            invoice_num.select2({
                placeholder: "-Select Invoice Number-"
            });
            invoice_num.prop('disabled', false);

            $('.nav-piv').click(function () {
                $('#inv-all').prop('disabled', true);
                invoice_num.select2({
                    placeholder: "-Select Invoice Number-"
                });
            });

            $('.nav-sum').click(function () {
                $('#inv-all').prop('disabled', false);
                invoice_num.select2({
                    placeholder: "-Select Invoice Number-"
                });
            });

            var div_sum = $('.sum-table');
            var div_sum_dom = $('.sum-table-dom');
            var div_sum_int = $('.sum-table-int');
            var div_inv = $('.inv-table');
            var div_det = $('.det-table');
            var div_piv = $('.piv-table');
            var div_sum_total_dom = $('.sum-table-total-dom');
            var div_sum_total_int = $('.sum-table-total-int');

            var sum_table_dom = $('#sum-table-dom');
            var sum_table_int = $('#sum-table-int');
            var inv_table = $('#inv-table');
            var det_table = $('#det-table');
            var piv_table = $('#piv-table');
            var overlay = $('.overlay');

            var id = "";

            invoice_num.change(function () {
                id = $(this).val();
                datepicker.prop('disabled', true);
                overlay.prop('hidden', false);

                div_sum.prop('hidden', false);
                div_sum_dom.prop('hidden', true);
                div_sum_int.prop('hidden', true);
                div_inv.prop('hidden', true);
                div_det.prop('hidden', true);
                div_piv.prop('hidden', true);
                div_sum_total_dom.prop('hidden', true);
                div_sum_total_int.prop('hidden', true);

                if(id === 'all'){
                    $('.nav-piv').prop('style', 'display: none');
                    datepicker.prop('disabled', false);
                }
                else{
                    $('.nav-piv').prop('style', 'display: block');
                    $('.download').prop('hidden', false);
                    $('a#download').attr('href', 'download-charge-tnc/'+id);
                }

//                Summary
                summary_table(id);

                var from_period = $('#from-period');
                var to_period = $('#to-period');

                from_period.change(function () {
                    if(to_period.val() !== ''){
                        div_sum_dom.prop('hidden', true);
                        div_sum_int.prop('hidden', true);
                        div_sum_total_dom.prop('hidden', true);
                        div_sum_total_int.prop('hidden', true);

                        var fp_val = from_period.val().split("-").reverse().join("-");
                        var tp_val = to_period.val().split("-").reverse().join("-");

                        find_period(fp_val, tp_val);
                    }
                });

                to_period.change(function () {
                    if(from_period.val() !== ''){
                        div_sum_dom.prop('hidden', true);
                        div_sum_int.prop('hidden', true);
                        div_sum_total_dom.prop('hidden', true);
                        div_sum_total_int.prop('hidden', true);

                        var fp_val = from_period.val().split("-").reverse().join("-");
                        var tp_val = to_period.val().split("-").reverse().join("-");

                        find_period(fp_val, tp_val);
                    }
                });

                $('.update-type').click(function () {
                    var m_type_old = $('#m-type-old').val();
                    var m_type = $('#m-type').val();

                    overlay.prop('hidden', false);

                    $.ajax({
                        url: '{{url('charge-tnc-summary-update-type')}}',
                        type: 'POST',
                        data: {
                            'id': id,
                            'type_old': m_type_old,
                            'type': m_type,
                            '_token' : '{{csrf_token()}}'
                        },

                        success: function (response) {
                            overlay.prop('hidden', true);
                            summary_table(id);
                            pivot(id);
                        }
                    });
                });

                function summary_table(id) {
                    div_sum_dom.prop('hidden', true);
                    div_sum_total_dom.prop('hidden', true);
                    div_sum_int.prop('hidden', true);
                    div_sum_total_int.prop('hidden', true);

                    $.ajax({
                        url: '{{url('charge-tnc-summary')}}',
                        type: 'GET',
                        data: {
                            'id': id,
                            'flight_type': 'DOM'
                        },

                        success: function (data) {
                            if (id === 'all') {
                                from_period.val('');
                                to_period.val('');
                            }
                            else if (data.length !== 0) {
                                from_period.val((data[0].from_period).split("-").reverse().join("-"));
                                to_period.val((data[0].to_period).split("-").reverse().join("-"));
                            }

                            if (data.length > 0 && data[0].flight_type === 'DOM') {
                                summary(data, 'DOM');

                                div_sum_dom.prop('hidden', false);
                                div_sum_total_dom.prop('hidden', false);
                            }
                        }
                    });

                    $.ajax({
                        url: '{{url('charge-tnc-summary')}}',
                        type: 'GET',
                        data: {
                            'id': id,
                            'flight_type': 'INT'
                        },

                        success: function (data) {
                            if (id === 'all') {
                                from_period.val('');
                                to_period.val('');
                            }
                            else if (data.length !== 0) {
                                from_period.val((data[0].from_period).split("-").reverse().join("-"));
                                to_period.val((data[0].to_period).split("-").reverse().join("-"));
                            }

                            if (data.length > 0 && data[0].flight_type === 'INT') {
                                summary(data, 'INT');

                                div_sum_int.prop('hidden', false);
                                div_sum_total_int.prop('hidden', false);
                            }
                        }
                    });
                }

                function summary(data, flight_type){
                    var sum_charter = 0;
                    var sum_regular = 0;
                    var sum_hajj = 0;
                    var row_num = 1;
                    var total_charge = 0;
                    var table_content = " ";
                    var table_total = " ";

                    if(flight_type === 'DOM'){
                        table_content +='<h4><i class="fa fa-home"></i> Domestic</h4>';
                    }
                    else{
                        table_content ='<h4><i class="fa fa-globe"></i> International</h4>';
                    }

                    table_content +='<table class="table table-striped table-bordered" style="text-align: center">' +
                        '<thead>' +
                        '<tr>' +
                        '<th class="col-md-1" style="text-align: center">No</th>' +
                        '<th class="col-md-4" style="text-align: center">Service</th>' +
                        '<th class="col-md-3" style="text-align: center">Type</th>' +
                        '<th style="text-align: center">' +
                        'Total Charge ' +
                        '</th>' +
                        '</tr>' +
                        '</thead>';

                    table_content += '<tbody>';

//                    Charter
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Charter'){
                            sum_charter += data[i].total_charge;
                        }
                    }

                    var total_charter = sum_charter.toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Charter'){
                            table_content+='<tr class="table">' +
                                '<td colspan="3"><strong>Charter Flight (Cost Centre : JKTDU)</strong></td>' +
                                '<td style="text-align: right"><span style="float: left">IDR</span>  '+total_charter+'</td>' +
                                '</tr>';
                            break;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Charter'){
                            total_charge = parseFloat(parseFloat(data[i].total_charge).toFixed(2)).toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                            table_content+='<tr class="table">' +
                                '<td>'+(row_num++)+'</td>' +
                                '<td>'+data[i].service+'</td>' +
                                '<td>' +
                                '<input type="hidden" id="actype" value="'+data[i].type+'">' +
                                '<a href="#update-type" data-toggle="modal" class="sum-type">'+data[i].type+'</a>' +
                                '</td>' +
                                '<td style="text-align: right"><span style="float: left">IDR </span><span class="numbers"></i> '+total_charge+'</span></td>' +
                                '</tr>';
                        }
                    }

//                    Hajj Flight
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Hajj'){
                            sum_hajj += data[i].total_charge;
                        }
                    }

                    var total_hajj = sum_hajj.toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Hajj'){
                            table_content+='<tr class="table">' +
                                '<td colspan="3"><strong>Hajj Flight (Cost Centre : JKTDU)</strong></td>' +
                                '<td style="text-align: right"><span style="float: left">IDR</span>  '+total_hajj+'</td>' +
                                '</tr>';
                            break;
                        }
                    }

                    row_num = 1;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Hajj'){
                            total_charge = parseFloat(parseFloat(data[i].total_charge).toFixed(2)).toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                            table_content+='<tr class="table">' +
                                '<td>'+(row_num++)+'</td>' +
                                '<td>'+data[i].service+'</td>' +
                                '<td>' +
                                '<input type="hidden" id="actype" value="'+data[i].type+'">' +
                                '<a href="#update-type" data-toggle="modal" class="sum-type">'+data[i].type+'</a>' +
                                '</td>' +
                                '<td style="text-align: right"><span style="float: left">IDR </span><span class="numbers"></i> '+total_charge+'</span></td>' +
                                '</tr>';
                        }
                    }

//                    Flight Training
                    var sum_training = 0;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Flight Training'){
                            sum_training += data[i].total_charge;
                        }
                    }

                    var total_training = sum_training.toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Flight Training'){
                            table_content+='<tr class="table">' +
                                '<td colspan="3"><strong>Flight Training (Cost Centre : JKTVZO)</strong></td>' +
                                '<td style="text-align: right"><span style="float: left">IDR</span>  '+total_training+'</td>' +
                                '</tr>';
                            break;
                        }
                    }

                    row_num = 1;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Flight Training'){
                            total_charge = parseFloat(parseFloat(data[i].total_charge).toFixed(2)).toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                            table_content+='<tr class="table">' +
                                '<td>'+(row_num++)+'</td>' +
                                '<td>'+data[i].service+'</td>' +
                                '<td>' +
                                '<input type="hidden" id="actype" value="'+data[i].type+'">' +
                                '<a href="#update-type" data-toggle="modal" class="sum-type">'+data[i].type+'</a>' +
                                '</td>' +
                                '<td style="text-align: right"><span style="float: left">IDR </span><span class="numbers"></i> '+total_charge+'</span></td>' +
                                '</tr>';
                        }
                    }

//                    Test Flight
                    var sum_test = 0;

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Test Flight'){
                            sum_test += data[i].total_charge;
                        }
                    }

                    var total_test = sum_test.toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Test Flight'){
                            table_content+='<tr class="table">' +
                                '<td colspan="3"><strong>Test Flight (Cost Centre : JKTML)</strong></td>' +
                                '<td style="text-align: right"><span style="float: left">IDR</span>  '+total_test+'</td>' +
                                '</tr>';
                            break;
                        }
                    }

                    row_num = 1;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Test Flight'){
                            total_charge = parseFloat(parseFloat(data[i].total_charge).toFixed(2)).toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                            table_content+='<tr class="table">' +
                                '<td>'+(row_num++)+'</td>' +
                                '<td>'+data[i].service+'</td>' +
                                '<td>' +
                                '<input type="hidden" id="actype" value="'+data[i].type+'">' +
                                '<a href="#update-type" data-toggle="modal" class="sum-type">'+data[i].type+'</a>' +
                                '</td>' +
                                '<td style="text-align: right"><span style="float: left">IDR </span><span class="numbers"></i> '+total_charge+'</span></td>' +
                                '</tr>';
                        }
                    }

//                    Delivery Flight
                    var sum_deliv = 0;

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Test Flight'){
                            sum_deliv += data[i].total_charge;
                        }
                    }

                    var total_deliv = sum_deliv.toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Delivery Flight'){
                            table_content+='<tr class="table">' +
                                '<td colspan="3"><strong>Delivery Flight (Cost Centre : JKTDH)</strong></td>' +
                                '<td style="text-align: right"><span style="float: left">IDR</span>  '+total_deliv+'</td>' +
                                '</tr>';
                            break;
                        }
                    }

                    row_num = 1;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Delivery Flight'){
                            total_charge = parseFloat(parseFloat(data[i].total_charge).toFixed(2)).toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                            table_content+='<tr class="table">' +
                                '<td>'+(row_num++)+'</td>' +
                                '<td>'+data[i].service+'</td>' +
                                '<td>' +
                                '<input type="hidden" id="actype" value="'+data[i].type+'">' +
                                '<a href="#update-type" data-toggle="modal" class="sum-type">'+data[i].type+'</a>' +
                                '</td>' +
                                '<td style="text-align: right"><span style="float: left">IDR </span><span class="numbers"></i> '+total_charge+'</span></td>' +
                                '</tr>';
                        }
                    }

//                    Regular
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Regular'){
                            sum_regular += data[i].total_charge;
                        }
                    }

                    var total_regular = parseFloat(parseFloat(sum_regular).toFixed(2)).toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                    table_content+='<tr class="table">' +
                        '<td colspan="3"><strong>Regular Flight (Cost Centre : JKTOS)</strong></td>' +
                        '<td style="text-align: right"><span style="float: left">IDR</span>  '+total_regular+'</td>' +
                        '</tr>';

                    row_num = 1;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Regular'){
                            total_charge = parseFloat(parseFloat(data[i].total_charge).toFixed(2)).toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                            table_content+='<tr class="table">' +
                                '<td>'+(row_num++)+'</td>' +
                                '<td>'+data[i].service+'</td>' +
                                '<td>' +
                                '<input type="hidden" id="actype" value="'+data[i].type+'">' +
                                '<a href="#update-type" data-toggle="modal" class="sum-type">'+data[i].type+'</a>' +
                                '</td>' +
                                '<td style="text-align: right"><span style="float: left">IDR </span><span class="numbers"></i> '+total_charge+'</span></td>' +
                                '</tr>';
                        }
                    }

                    var sum_total = 0;

                    for(var i=0;i<data.length;i++){
                        sum_total += data[i].total_charge;
                    }

                    var grand_total = parseFloat(parseFloat(sum_total).toFixed(2)).toLocaleString(undefined, { minimumFractionDigits: 2 }).replace(/[,.]/g, function (m) {return m === ',' ? '.' : ',';});

                    table_content+='<tr class="table">' +
                        '<td colspan="3"><strong>Grand Total</strong></td>' +
                        '<td style="text-align: right"><span style="float: left">IDR</span>  '+grand_total+'</td>' +
                        '</tr>'+
                        '</tbody>'+
                        '</table>';

//                    Total Flight per Service
                    if(flight_type === 'DOM'){
                        table_total +='<h4 style="text-align: center"><i class="fa fa-plane"></i></h4>';
                    }
                    else{
                        table_total ='<h4 style="text-align: center"><i class="fa fa-plane"></i></h4>';
                    }

                    table_total += '<table class="table table-striped table-bordered" style="text-align: center">'+
                        '<thead>' +
                        '<tr>' +
                        '<th style="text-align: center">Total Flight</th>' +
                        '<tr>'+
                        '</thead>'+
                        '<tbody>';

//                    Total Charter
                    var tot_chart = 0;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Charter'){
                            tot_chart += data[i].total_flight;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Charter'){
                            table_total+='<tr class="table">' +
                                '<td>'+tot_chart+' Flight</td>'+
                                '</tr>';
                            break;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Charter'){
                            table_total+='<tr class="table">' +
                                '<td>'+data[i].total_flight+' Flight</td>' +
                                '</tr>';
                        }
                    }

//                    Total Hajj Flight
                    var tot_hajj = 0;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Hajj'){
                            tot_hajj += data[i].total_flight;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Hajj'){
                            table_total+='<tr class="table">' +
                                '<td>'+tot_hajj+' Flight</td>'+
                                '</tr>';
                            break;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Hajj'){
                            table_total+='<tr class="table">' +
                                '<td>'+data[i].total_flight+' Flight</td>' +
                                '</tr>';
                        }
                    }

//                    Total Flight Training
                    var tot_training = 0;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Flight Training'){
                            tot_training += data[i].total_flight;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Flight Training'){
                            table_total+='<tr class="table">' +
                                '<td>'+tot_training+' Flight</td>'+
                                '</tr>';
                            break;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Flight Training'){
                            table_total+='<tr class="table">' +
                                '<td>'+data[i].total_flight+' Flight</td>' +
                                '</tr>';
                        }
                    }

//                    Total Test Flight
                    var tot_test = 0;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Test Flight'){
                            tot_test += data[i].total_flight;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Test Flight'){
                            table_total+='<tr class="table">' +
                                '<td>'+tot_test+' Flight</td>'+
                                '</tr>';
                            break;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Test Flight'){
                            table_total+='<tr class="table">' +
                                '<td>'+data[i].total_flight+' Flight</td>' +
                                '</tr>';
                        }
                    }

//                    Total Delivery Flight
                    var tot_deliv = 0;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Delivery Flight'){
                            tot_deliv += data[i].total_flight;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Delivery Flight'){
                            table_total+='<tr class="table">' +
                                '<td>'+tot_deliv+' Flight</td>'+
                                '</tr>';
                            break;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Delivery Flight'){
                            table_total+='<tr class="table">' +
                                '<td>'+data[i].total_flight+' Flight</td>' +
                                '</tr>';
                        }
                    }

//                    Total Regular
                    var tot_reg = 0;
                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Regular'){
                            tot_reg += data[i].total_flight;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Regular'){
                            table_total+='<tr class="table">' +
                                '<td>'+tot_reg+' Flight</td>'+
                                '</tr>';
                            break;
                        }
                    }

                    for(var i=0;i<data.length;i++){
                        if(data[i].service === 'Regular'){
                            table_total+='<tr class="table">' +
                                '<td>'+data[i].total_flight+' Flight</td>' +
                                '</tr>';
                        }
                    }

//                    Grand Total Flight
                    var grand_tot = 0;
                    for(var i=0;i<data.length;i++){
                        grand_tot += data[i].total_flight;
                    }

                    table_total+='<tr class="table">' +
                        '<td>'+grand_tot+' Flight</td>' +
                        '</tr>';

                    table_total += '<tbody></table>';

                    if(flight_type === 'DOM'){
                        div_sum_dom.html('');
                        div_sum_dom.append(table_content);
                        div_sum_total_dom.html('');
                        div_sum_total_dom.append(table_total)
                    }
                    else{
                        div_sum_int.html('');
                        div_sum_int.append(table_content);
                        div_sum_total_int.html('');
                        div_sum_total_int.append(table_total)
                    }

                    $('.sum-type').click(function () {
                        var tr = $(this).closest('tr');
                        var ac_type = tr.find('#actype').val();
                        $('#m-type-old').val(ac_type);
                        $('#m-type').val(ac_type);
                    });
                }

                function find_period(from_period, to_period){
                    $.ajax({
                        url: '{{url('charge-tnc-summary-period')}}',
                        type: 'GET',
                        data: {
                            'from_period' : from_period,
                            'to_period' : to_period,
                            'flight_type': 'DOM'
                        },

                        success: function (data) {
                            summary(data, 'DOM');

                            div_sum_dom.prop('hidden', false);
                            div_sum_total_dom.prop('hidden', false);
                        }
                    });

                    $.ajax({
                        url: '{{url('charge-tnc-summary-period')}}',
                        type: 'GET',
                        data: {
                            'from_period' : from_period,
                            'to_period' : to_period,
                            'flight_type': 'INT'
                        },

                        success: function (data) {
                            summary(data, 'INT');

                            div_sum_int.prop('hidden', false);
                            div_sum_total_int.prop('hidden', false);
                        }
                    });
                }

//                Invoice
                $.ajax({
                    url : '{{url('charge-tnc-invoices')}}',
                    type : 'GET',
                    data : {
                        'id' : id
                    },

                    success: function(data){
                        var dataSet = [];

                        for(var i=0;i<data.length;i++){
                            dataSet[i] = [
                                data[i].flight_num,
                                data[i].type,
                                data[i].service,
                                data[i].ac_register,
                                data[i].date,
                                data[i].departure,
                                data[i].arrival,
                                data[i].time_out,
                                data[i].category,
                                data[i].total_mtow,
                                data[i].terminal_charge
                            ];
                        }

                        $('#inv-table').DataTable( {
                            "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                            "destroy": true,
                            data: dataSet,
                            columns: [
                                { title: "Flight No" },
                                { title: "Type" },
                                { title: "Service" },
                                { title: "AC Register" },
                                { title: "Date" },
                                { title: "Dept" },
                                { title: "Arr" },
                                { title: "Time Out" },
                                { title: "Category" },
                                { title: "Total MTOW" },
                                { title: "Terminal Charge(IDR)" }
                            ]
                        } );

                        div_inv.prop('hidden', false);
                    }
                });

//                Flight Detail
                details(id);

//                Pivot
                pivot(id);

//                Service
                $('#m-service').keypress(function(e) {
                    if(e.which === 13) {
                        var m_id = $('#m-id').val();
                        var m_service = $('#m-service').val();
                        $('.close').click();

                        div_piv.prop('hidden', true);

                        $.ajax({
                            url: '{{url('charge-tnc-pivot-service')}}',
                            type: 'POST',
                            data: {
                                'id': m_id,
                                'service': m_service,
                                '_token' : '{{csrf_token()}}'
                            },

                            success: function (response) {}
                        });
                        pivot(id);
                    }
                });

                $('.update-service').click(function () {
                    var m_id = $('#m-id').val();
                    var m_service = $('#m-service').val();

                    overlay.prop('hidden', false);
                    div_piv.prop('hidden', true);

                    $.ajax({
                        url: '{{url('charge-tnc-pivot-service')}}',
                        type: 'POST',
                        data: {
                            'id': m_id,
                            'service': m_service,
                            '_token' : '{{csrf_token()}}'
                        },

                        success: function (response) {
                            overlay.prop('hidden', true);
                            pivot(id);
                        }
                    });
                });
            });

            function details(id){
                $.ajax({
                    url : '{{url('charge-tnc-details')}}',
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
                                data[i].leg_state
                            ];
                        }

                        det_table.DataTable( {
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
                                { title: "Leg State" }
                            ]
                        } );

                        div_det.prop('hidden', false);
                        overlay.prop('hidden', true);
                    }
                });
            }

            function pivot(id){
                var dataSet = [];

                $.ajax({
                    url: '{{url('charge-tnc-pivot')}}',
                    type: 'GET',
                    data: {
                        'id': id
                    },

                    success: function (data) {
                        for(var i=0, j=0 ; i<data.length ; i++, j++){

                            if(data[i].total_invoice === 0){
                                j--;
                            }
                            else if(data[i].difference < 0 && data[i].remarks !== "EXCESS" && !data[i].remarks.match(/^OK.*$/)){
                                dataSet[j] = [
                                    '<input type="hidden" id="id" value="'+data[i].id+'">'+
                                    '<a class="pivot-id" data-toggle="modal" href="#update-service" ' +
                                    'data-id="'+data[i].id+'" ' +
                                    'data-service="'+data[i].service+'">'+
                                    '<span>'+data[i].service+'</span></a>',
                                    '<span>' +
                                    '<input type="hidden" id="fn-number" value="'+data[i].fn_number+'">'+
                                    '<span id="flight-num">'+data[i].flight_num+'</span>',
                                    '<span id="departure">'+data[i].departure+'</span>',
                                    '<span id="arrival">'+data[i].arrival+'</span>',
                                    '<span id="type">'+data[i].type+'</span>',
                                    '<span id="total-invoice">'+data[i].total_invoice+'</span>',
                                    '<span id="total-detail">'+data[i].total_detail+'</span>',
                                    '<span id="diff">'+data[i].difference+'</span>',
                                    '<button class="pivot-detail btn btn-link" data-toggle="modal" data-target="#pivot-detail" style="color: #dd4b39"><i class="fa fa-warning"></i></button>' +
                                    '<button class="btn btn-link"><i class="fa fa-check" style="color: #337ab7"></i></button><span hidden>B</span>'
                                ];
                            }
                            else if(data[i].difference < 0 && !data[i].remarks.match(/^OK.*$/)){
                                dataSet[j] = [
                                    '<input type="hidden" id="id" value="'+data[i].id+'">'+
                                    '<a class="pivot-id" data-toggle="modal" href="#update-service" ' +
                                    'data-id="'+data[i].id+'" ' +
                                    'data-service="'+data[i].service+'">'+
                                    '<span>'+data[i].service+'</span></a>',
                                    '<span>' +
                                    '<input type="hidden" id="fn-number" value="'+data[i].fn_number+'">'+
                                    '<span id="flight-num">'+data[i].flight_num+'</span>',
                                    '<span id="departure">'+data[i].departure+'</span>',
                                    '<span id="arrival">'+data[i].arrival+'</span>',
                                    '<span id="type">'+data[i].type+'</span>',
                                    '<span id="total-invoice">'+data[i].total_invoice+'</span>',
                                    '<span id="total-detail">'+data[i].total_detail+'</span>',
                                    '<span id="diff">'+data[i].difference+'</span>',
                                    '<button class="pivot-detail btn btn-link" data-toggle="modal" data-target="#pivot-detail" style="color: #dd4b39"><i class="fa fa-warning"></i></button>' +
                                    '<span hidden>A</span>'
                                ];
                            }
                            else{
                                dataSet[j] = [
                                    '<input type="hidden" id="id" value="'+data[i].id+'">'+
                                    '<a class="pivot-id" data-toggle="modal" href="#update-service" ' +
                                    'data-id="'+data[i].id+'" ' +
                                    'data-service="'+data[i].service+'">'+
                                    '<span>'+data[i].service+'</span></a>',
                                    '<span>' +
                                    '<input type="hidden" id="fn-number" value="'+data[i].fn_number+'">'+
                                    '<span id="flight-num">'+data[i].flight_num+'</span>',
                                    '<span id="departure">'+data[i].departure+'</span>',
                                    '<span id="arrival">'+data[i].arrival+'</span>',
                                    '<span id="type">'+data[i].type+'</span>',
                                    '<span id="total-invoice">'+data[i].total_invoice+'</span>',
                                    '<span id="total-detail">'+data[i].total_detail+'</span>',
                                    '<span id="diff">'+data[i].difference+'</span>',
                                    '<button class="pivot-detail btn btn-link" data-toggle="modal" data-target="#pivot-detail"><i class="fa fa-check-square-o"></i></button>' +
                                    '<span hidden>C</span>'
                                ];
                            }
                        }
                        piv_table.DataTable( {
                            "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                            "destroy": true,
                            data: dataSet,
                            columns: [
                                { title: "Service" },
                                { title: "Flight No" },
                                { title: "Departure" },
                                { title: "Arrival" },
                                { title: "Type" },
                                { title: "Total Invoice" },
                                { title: "Total Detail" },
                                { title: "Difference" },
                                { title: "Status"}
                            ]
                        } );

                        overlay.prop('hidden', true);
                        div_piv.prop('hidden', false);
                    }
                });
            }

            piv_table.on('click', '.pivot-id', function () {
                $('#m-id').val($(this).data('id'));
                $('#m-service').val($(this).data('service'));
            });

            piv_table.on('click', '.pivot-detail', function () {
                var invoice_id = $('#invoice-num').val();

                var tr = $(this).closest('tr');

                var piv_id = tr.find('#id').val();
                var fn_number = tr.find('#fn-number').val();
                var flight_num = tr.find('#flight-num').html();
                var departure = tr.find('#departure').html();
                var arrival = tr.find('#arrival').html();
                var type = tr.find('#type').html();

                var div_piv_inv = $('.piv-inv');
                var div_piv_det = $('.piv-det');

                div_piv_inv.prop('hidden', true);
                div_piv_det.prop('hidden', true);

                $('.piv-id').val(piv_id);
                $('.remarks-correction').prop('disabled', true);
                $('#remarks-correction').val('');

//                Pivot Invoices
                $.ajax({
                    url : '{{url('charge-tnc-pivot-invoices')}}',
                    type : 'GET',
                    data : {
                        'id' : id,
                        'fn_number' : fn_number,
                        'departure' : departure,
                        'arrival' : arrival,
                        'type' : type,
                        'flight_num' : flight_num
                    },

                    success: function(data){
                        var table_content = "";

                        table_content += '<h4>Invoices</h4>';

                        if(data.length > 0) {
                            table_content += '<table class="inv-piv-table table table-striped table-bordered">' +
                                '<thead>' +
                                '<tr>' +
                                '<th>No</th>' +
                                '<th width="10%">Flight No</th>' +
                                '<th width="10%">Service</th>' +
                                '<th>Type</th>' +
                                '<th>AC Reg</th>' +
                                '<th>Date</th>' +
                                '<th>Time Out</th>' +
                                '<th>Dept</th>' +
                                '<th>Arr</th>' +
                                '<th>MTOW</th>' +
                                '<th>Category</th>' +
                                '<th>Charge</th>' +
                                '<th class="col-md-2">Remarks</th>' +
                                '</tr>' +
                                '</thead>' ;

                            table_content += '<tbody>';

                            for(var i=0;i<data.length;i++){
                                table_content+='<tr>' +
                                    '<td><input type="hidden" id="inv-id" value='+data[i].id+'>'+(i+1)+'</td>' +
                                    '<td>' +
                                    '<input type="hidden" id="fn-edit" class="form-control" value="'+data[i].flight_num+'">' +
                                    '<a href="#" onclick="return false" id="fn-view" class="piv-inv-fn">'+data[i].flight_num+'</a>' +
                                    '</td>' +
                                    '<td>' +
                                    '<input type="hidden" id="service-edit" class="form-control" value="'+data[i].service+'">' +
                                    '<a href="#" onclick="return false" id="service-view" class="piv-inv-service">'+data[i].service+'</a>' +
                                    '</td>' +
                                    '<td>'+data[i].type+'</td>' +
                                    '<td>'+data[i].ac_register+'</td>' +
                                    '<td class="date">'+data[i].date+'</td>' +
                                    '<td>'+data[i].time_out+'</td>' +
                                    '<td>'+data[i].departure+'</td>' +
                                    '<td>'+data[i].arrival+'</td>' +
                                    '<td>'+data[i].mtow+'</td>' +
                                    '<td>'+data[i].category+'</td>' +
                                    '<td>'+data[i].charge+'</td>' +
                                    '<td>' +
                                    '<div class="checkbox" style="margin: 1px">' +
                                    '<label>' +
                                    '<input type="checkbox" name="remember" id="inv-check-'+(i+1)+'" value="'+(i+1)+'">'+data[i].remarks +
                                    '</label>' +
                                    '</div>' +
                                    '</td>' +
                                    '</tr>';
                            }

                            table_content += '</tbody>' +
                                '</table>';

                            table_content += '<div class="col-md-6" style="float: right">' +
                                '<div class="checkbox col-md-2" style="margin-right: 10px">' +
                                '<label><input type="checkbox" id="check-auto" checked> Auto</label>' +
                                '</div>' +
                                '<div class="col-md-9"></div>'+
                                '<div class="input-group">' +
                                '<input type="text" class="remarks-correction form-control" id="remarks-correction" placeholder="Remarks correction..." style="text-align: center" disabled>' +
                                '<span class="input-group-btn">' +
                                '<button class="remarks-correction btn btn-primary" id="correction" type="button">' +
                                '<i class="fa fa-edit"></i> Correction' +
                                '</button>' +
                                '</span>' +
                                '</div>' +
                                '</div>' +
                                '</div>';

                            div_piv_inv.html('');
                            div_piv_inv.append(table_content);
                            div_piv_inv.prop('hidden', false);

                            var total_piv_inv = data.length;

                            for(var j=1 ; j<=total_piv_inv; j++){
                                var flag = false;

                                $('#inv-check-'+j+'').click(function () {
                                    for(var k=1 ; k<=total_piv_inv; k++){
                                        if($('#inv-check-'+k+'').is(':checked')){
                                            $('.remarks-correction').prop('disabled', false);
                                            flag = true;
                                            break;
                                        }
                                        else{
                                            $('.remarks-correction').prop('disabled', true);
                                            flag = false;
                                        }
                                    }
                                });

                                if(flag === true){
                                    break;
                                }
                            }

                            $('#correction').click(function () {
                                var remarks_correction = $('#remarks-correction').val();
                                var piv_id = $('.piv-id').val();
                                var check_auto = $('#check-auto');
                                var flag = 0;

                                if(check_auto.is(':checked')){
                                    flag = 1;
                                }
                                else{
                                    flag = 2;
                                }

                                if(remarks_correction === ""){
                                    new PNotify({
                                        title: "Error",
                                        text: "The remarks field is required.",
                                        type: 'error',
                                        delay: 2000
                                    });

                                }
                                else{
                                    $('.close').click();
                                    div_piv.prop('hidden', true);

                                    $.ajax({
                                        url: '{{url('charge-tnc-pivot-correction')}}',
                                        type: 'GET',
                                        data: {
                                            'id': piv_id,
                                            'remarks': remarks_correction,
                                            'flag' : flag
                                        },

                                        success: function (data) {
                                            for(var j=1 ; j<=total_piv_inv; j++){
                                                var checkbox = $('#inv-check-'+j+'');
                                                var tr = checkbox.closest('tr');
                                                var inv_id = tr.find('#inv-id').val();

                                                if(checkbox.is(':checked')){
                                                    $.ajax({
                                                        url: '{{url('charge-tnc-pivot-inv-update')}}',
                                                        type: 'GET',
                                                        data: {
                                                            'inv_id': inv_id,
                                                            'remarks': remarks_correction
                                                        },

                                                        success: function (response) {}
                                                    })
                                                }
                                                else{
                                                    if(check_auto.is(':checked')){
                                                        $.ajax({
                                                            url: '{{url('charge-tnc-pivot-inv-update')}}',
                                                            type: 'GET',
                                                            data: {
                                                                'inv_id': inv_id,
                                                                'remarks': 'OK'
                                                            },

                                                            success: function (response) {}
                                                        })
                                                    }
                                                }
                                            }

                                            pivot(id);

                                            new PNotify({
                                                title: "Update",
                                                text: data.success,
                                                type: 'success',
                                                delay: 2000
                                            });
                                        }
                                    });
                                }
                            });

                            $('.piv-inv-fn').click(function () {
                                PNotify.removeAll();
                                new PNotify({
                                    title: 'Notice',
                                    text: 'Press Enter to update the value.'
                                });

                                var tr = $(this).closest('tr');

                                tr.find('#fn-view').prop('style', 'display: none');
                                tr.find('#fn-edit').prop('type', 'text');

                                invoice_id = $('#invoice-num').val();
                                var inv_det_id = tr.find('#inv-id').val();
                                var date = tr.find('.date').html();

                                tr.find('#fn-edit').keypress(function(e) {
                                    PNotify.removeAll();
                                    if(e.which === 13) {
                                        var fligt_num_update = tr.find('#fn-edit').val();
                                        var fn_no = "";

                                        for(var i=0 ; i<fligt_num_update.length ; i++){
                                            if(isNaN(fligt_num_update[i])){
                                                fn_no = fligt_num_update.substr(i+1);
                                            }
                                        }

                                        if(fn_no.substr(0, 1) === '0'){
                                            fn_no = fn_no.substr(1);
                                            if(fn_no.substr(0, 1) === '0'){
                                                fn_no = fn_no.substr(1);
                                            }
                                        }

                                        $.ajax({
                                            url : '{{url('charge-tnc-pivot-inv-fn')}}',
                                            type : 'GET',
                                            data : {
                                                'invoice_id' : invoice_id,
                                                'inv_det_id' : inv_det_id,
                                                'piv_id' : piv_id,
                                                'departure' : departure,
                                                'arrival' : arrival,
                                                'fligt_num_update' : fligt_num_update,
                                                'fn_number' : fn_no,
                                                'type' : type,
                                                'date' : date
                                            },

                                            success: function(response){
                                                if(typeof response.success === 'undefined'){
                                                    new PNotify({
                                                        title: "Warning!",
                                                        text: response.error,
                                                        type: 'error',
                                                        delay: 2000
                                                    });

                                                    div_piv.prop('hidden', false);
                                                }
                                                else{
                                                    new PNotify({
                                                        title: "Update",
                                                        text: response.success,
                                                        type: 'success',
                                                        delay: 2000
                                                    });

                                                    $('.close').click();
                                                    div_piv.prop('hidden', true);
                                                    overlay.prop('hidden', false);
                                                    pivot(invoice_id);
                                                }
                                            }
                                        });
                                    }
                                });
                            });

                            $('.piv-inv-service').click(function () {
                                PNotify.removeAll();
                                new PNotify({
                                    title: 'Notice',
                                    text: 'Press Enter to update the value.'
                                });

                                var tr = $(this).closest('tr');

                                tr.find('#service-view').prop('style', 'display: none');
                                tr.find('#service-edit').prop('type', 'text');

                                tr.find('#service-edit').keypress(function(e) {
                                    PNotify.removeAll();
                                    if(e.which === 13) {
                                        var id = tr.find('#inv-id').val();
                                        var service = tr.find('#service-edit').val();

                                        $.ajax({
                                            url : '{{url('charge-tnc-pivot-inv-service')}}',
                                            type : 'GET',
                                            data : {
                                                'id' : id,
                                                'service' : service
                                            },

                                            success: function(response){
                                                new PNotify({
                                                    title: "Update",
                                                    text: response.success,
                                                    type: 'success',
                                                    delay: 2000
                                                });

                                                $('.close').click();
                                                div_piv.prop('hidden', true);
                                                overlay.prop('hidden', false);
                                                pivot(invoice_id);
                                            }
                                        });
                                    }
                                });
                            });
                        }
                    }
                });

//                Pivot Details
                $.ajax({
                    url : '{{url('charge-tnc-pivot-details')}}',
                    type : 'GET',
                    data : {
                        'id' : id,
                        'fn_number' : fn_number,
                        'departure' : departure,
                        'arrival' : arrival,
                        'type' : type
                    },

                    success: function(data){
                        if(data.length > 0) {
                            var table_content = "";

                            table_content += '<h4>Details</h4>';

                            table_content += '<table class="det-piv-table table table-striped table-bordered">' +
                                '<thead>' +
                                '<tr>' +
                                '<th>No</th>' +
                                '<th>Flight No</th>' +
                                '<th>Service</th>' +
                                '<th>Type</th>' +
                                '<th>AC Reg</th>' +
                                '<th>Dept Act Date</th>' +
                                '<th>Arr Act Date</th>' +
                                '<th>Time Out</th>' +
                                '<th>Dept</th>' +
                                '<th>Arr</th>' +
                                '<th>Leg State</th>' +
                                '<th>Check</th>' +
                                '</tr>' +
                                '</thead>' ;

                            table_content += '<tbody>';

                            for(var i=0;i<data.length;i++){
                                if(data[i].leg_state === null){
                                    data[i].leg_state = '';
                                }

                                var dep_dt = data[i].dep_act_dt.substring(0, 10);
                                var arr_dt = data[i].arr_act_dt.substring(0, 10);
                                var time_out = data[i].arr_act_dt.substring(11);

                                table_content+='<tr>' +
                                    '<td>'+(i+1)+'</td>' +
                                    '<input type="hidden" class="fd-id" value="'+data[i].id+'">' +
                                    '<td>'+data[i].fn_number+'</td>';

                                if(data[i].type !== type){
                                    table_content+='<td style="color: #dd4b39"><strong>'+data[i].type+'</strong></td>';
                                }
                                else{
                                    table_content+='<td>'+data[i].type+'</td>';
                                }

                                table_content+='<td>'+data[i].service+'</td>' +
                                    '<td>'+data[i].ac_registration+'</td>' +
                                    '<td>'+dep_dt+'</td>' +
                                    '<td>'+arr_dt+'</td>' +
                                    '<td>'+time_out+'</td>';

                                if(data[i].dep_ap_act !== departure){
                                    table_content+='<td style="color: #dd4b39"><strong>'+data[i].dep_ap_act+'</strong></td>';
                                }
                                else{
                                    table_content+='<td>'+data[i].dep_ap_act+'</td>';
                                }

                                if(data[i].arr_ap_act !== arrival){
                                    table_content+='<td style="color: #dd4b39"><strong>'+data[i].arr_ap_act+'</strong></td>';
                                }
                                else{
                                    table_content+='<td>'+data[i].arr_ap_act+'</td>';
                                }

                                table_content+= '<td>'+data[i].leg_state+'</td>' ;

                                if(data[i].flight_check_tnc === "unbilled"){
                                    table_content+='<td><button class="flight-cross btn-link" style="color: #dd4b39"><i class="fa fa-times"></i></button></td>'+
                                        '</tr>';
                                }
                                else{
                                    table_content+='<td><button class="flight-check btn-link"><i class="fa fa-check-square-o"></i></button></td>'+
                                        '</tr>';
                                }
                            }

                            table_content += '</tbody>' +
                                '</table>';

                            div_piv_det.html('');
                            div_piv_det.append(table_content);
                            div_piv_det.prop('hidden', false);

                            $('.flight-cross').click(function () {
                                var tr = $(this).closest('tr');
                                var id = tr.find('.fd-id').val();
                                invoice_id = $('#invoice-num').val();

                                tr.find('.fa').prop('class', 'fa fa-check-square-o');
                                tr.find('.btn').prop('style', 'color: ');

                                $.ajax({
                                    url : '{{url('charge-tnc-pivot-check')}}',
                                    type : 'GET',
                                    data : {
                                        'invoice_id' : invoice_id,
                                        'id' : id,
                                        'flag' : 1
                                    },

                                    success: function(response){
                                        overlay.prop('hidden', false);

                                        details(invoice_id);

                                        new PNotify({
                                            title: "Update",
                                            text: response.success,
                                            type: 'success',
                                            delay: 2000
                                        });
                                    }
                                })
                            });

                            $('.flight-check').click(function () {
                                var tr = $(this).closest('tr');
                                var id = tr.find('.fd-id').val();
                                invoice_id = $('#invoice-num').val();

                                tr.find('.fa').prop('class', 'fa fa-times');
                                tr.find('.btn').prop('style', 'color: #dd4b39');

                                $.ajax({
                                    url : '{{url('charge-tnc-pivot-check')}}',
                                    type : 'GET',
                                    data : {
                                        'invoice_id' : invoice_id,
                                        'id' : id,
                                        'flag' : 2
                                    },

                                    success: function(response){
                                        overlay.prop('hidden', false);

                                        details(invoice_id);

                                        new PNotify({
                                            title: "Update",
                                            text: response.success,
                                            type: 'success',
                                            delay: 2000
                                        });
                                    }
                                })
                            });
                        }
                    }
                });
            });
        });
    </script>
@stop