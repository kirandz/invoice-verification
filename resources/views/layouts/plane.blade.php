<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="{{ asset('images/invest-icon.ico') }}"/>

    <title>Navigation Service Invoice Verification System</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="{{asset("bower_components/bootstrap/dist/css/bootstrap.min.css")}}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{asset("bower_components/font-awesome/css/font-awesome.min.css")}}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{asset("bower_components/Ionicons/css/ionicons.min.css")}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset("dist/css/AdminLTE.min.css")}}">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{asset("dist/css/skins/_all-skins.min.css")}}">
    <!-- jvectormap -->
    <link rel="stylesheet" href="{{asset("bower_components/jvectormap/jquery-jvectormap.css")}}">
    <!-- Date Picker -->
    <link rel="stylesheet" href="{{asset("bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css")}}">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="{{asset("plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css")}}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{asset("bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css")}}">
    <!-- Pace style -->
    <link rel="stylesheet" href="{{asset("plugins/pace/pace.min.css")}}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{asset("bower_components/select2/dist/css/select2.min.css")}}">
    <!-- PNotify -->
    <link rel="stylesheet" href="{{asset("vendors/pnotify/pnotify.custom.min.css")}}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <!-- jQuery 3 -->
    <script src="{{asset("bower_components/jquery/dist/jquery.min.js")}}"></script>
    <!-- datepicker -->
    <script src="{{asset("bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js")}}"></script>
    <!-- DataTables -->
    <script src="{{asset("bower_components/datatables.net/js/jquery.dataTables.min.js")}}"></script>
    <script src="{{asset("bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js")}}"></script>
    <!-- PACE -->
    <script src="{{asset("bower_components/PACE/pace.min.js")}}"></script>
    <!-- Select2 -->
    <script src="{{asset("bower_components/select2/dist/js/select2.full.min.js")}}"></script>
    <!-- Chart.js -->
    <script src="{{asset("vendors/Chart.js/dist/Chart.min.js")}}"></script>
    <!-- PNotify -->
    <script src="{{asset("vendors/pnotify/pnotify.custom.min.js")}}"></script>

</head>
<body class="hold-transition skin-blue sidebar-mini">

@yield('body')
<!-- Bootstrap 3.3.7 -->
<script src="{{asset("bower_components/bootstrap/dist/js/bootstrap.min.js")}}"></script>
<!-- Sparkline -->
<script src="{{asset("bower_components/jquery-sparkline/dist/jquery.sparkline.min.js")}}"></script>
<!-- jvectormap -->
<script src="{{asset("plugins/jvectormap/jquery-jvectormap-1.2.2.min.js")}}"></script>
<script src="{{asset("plugins/jvectormap/jquery-jvectormap-world-mill-en.js")}}"></script>
<!-- jQuery Knob Chart -->
<script src="{{asset("bower_components/jquery-knob/dist/jquery.knob.min.js")}}"></script>
<!-- Bootstrap WYSIHTML5 -->
<script src="{{asset("plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js")}}"></script>
<!-- Slimscroll -->
<script src="{{asset("bower_components/jquery-slimscroll/jquery.slimscroll.min.js")}}"></script>
<!-- FastClick -->
<script src="{{asset("bower_components/fastclick/lib/fastclick.js")}}"></script>
<!-- AdminLTE App -->
<script src="{{asset("dist/js/adminlte.min.js")}}"></script>

<script>
    $(document).ajaxStart(function() { Pace.restart(); });
</script>

</body>
</html>