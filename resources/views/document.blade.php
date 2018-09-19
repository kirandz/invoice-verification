@extends('layouts.app')

@section('page_heading', 'Document')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Document</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list-ul"></i> List</h3>

                        <div class="box-tools pull-right">
                            <button class="trigger-upload btn btn-link"><i class="fa fa-upload"></i> Upload Document</button>
                            <form action="{{url('doc-upload')}}" method="POST" enctype="multipart/form-data" hidden>
                                {{csrf_field()}}
                                <input type="file" class="upload" name="document" accept="application/pdf">
                                <button type="submit" class="upload-submit"></button>
                            </form>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th class="col-md-11">Document Name</th>
                                <th class="col-md-1" style="text-align: center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($documents) > 0)
                                @foreach($documents as $document)
                                    <tr>
                                        <input type="hidden" class="id" value="{{$document->id}}">
                                        <td><a href="#" class="doc-name">{{$document->name}}</a></td>
                                        <td style="text-align: center">
                                            <button class="btn-link delete"><i class="fa fa-trash" data-toggle="tooltip" title="Delete"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                               <tr>
                                   <td colspan="2" style="text-align: center">No data available in table</td>
                               </tr>
                            @endif

                            </tbody>
                        </table>

                        <br>
                        <div id="document" hidden>
                            <embed id="embed">
                        </div>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
    </section>

    <?php $flag = 0;?>

    @if(session()->has('message'))
        <?php $flag = 1?>
        <i id="msg-success" hidden>{{ Session::get('message') }}</i>
    @elseif ($errors->first())
        <?php $flag = 2?>
        <i id="msg-error" hidden>{{ $errors->first() }}</i>
    @endif

    <script>
        var flag = <?php echo $flag ?>

        $(document).ready(function() {
            var msg = "";

            if(flag === 1){
                msg = $('#msg-success').html();

                new PNotify({
                    title: 'Success',
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

            $('.trigger-upload').click(function () {
                var upload = $('.upload');
                upload.click();

                upload.change(function () {
                    $('.upload-submit').click();
                });
            });

            $('.doc-name').click(function () {
                var tr = $(this).closest('tr');
                var name = tr.find('.doc-name').text();
                var document = $('#document');

                document.show();

                var url = '{{ url("/files", "id") }}';
                url = url.replace('id', name);

                $('#embed').remove();
                var op = '<embed src="'+url+'#page=1&zoom=75" type="application/pdf" width="100%" height="800px" id="embed">';
                document.append(op);
            });

            $('.delete').click(function () {
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
                        url : '{{url('doc-delete')}}',
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
        });

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@stop