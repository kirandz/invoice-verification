@extends('layouts.app')

@section('page_heading', 'User Profile')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{url('home')}}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">User Profile</li>
    </ol>
@endsection

@section('section')
    <section class="content">
        <div class="row">
            <div class="col-md-5">
                <div class="box box-primary">
                    <div class="box-body box-profile">
                        <img class="profile-user-img img-responsive img-circle" src="{{asset('images/user.png')}}" alt="User profile picture">

                        <h3 class="profile-username text-center">{{Auth::user()->name}}</h3>

                        <form role="form" action="{{url('user-update')}}" method="post">
                            {{csrf_field()}}
                            <input type="hidden" name="id" value="{{Auth::user()->id}}">

                            <div class="box-body">
                                <div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{Auth::user()->name}}">

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="form-group {{ $errors->has('email') ? ' has-error' : '' }}">
                                    <label for="email">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{Auth::user()->email}}">

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="form-group {{ $errors->has('password') ? ' has-error' : '' }}">
                                    <label for="password">New password</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <!-- /.box-body -->

                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary" style="width: 100%"><i class="fa fa-save"></i> Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php $flag = 0;?>

    @if ($message = Session::get('success'))
        <?php $flag = 1?>
        <i id="msg-success" hidden>{{ Session::get('success') }}</i>
    @endif

    <script>
        var flag = <?php echo $flag?>

        $(document).ready(function () {
            var msg = "";

            if(flag === 1) {
                msg = $('#msg-success').html();

                new PNotify({
                    title: 'Success!',
                    text: msg,
                    type: 'success',
                    delay: 2000
                });
            }
        });
    </script>
@stop