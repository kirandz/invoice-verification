@extends('layouts.plane')

@section('body')
    <div class="wrapper">

        <header class="main-header">
            <!-- Logo -->
            <a href="{{url('')}}" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini">
                    <img src="{{asset('images/invest-logo.png')}}" height="30px" width="30px" style="margin-top: 6px; margin-bottom: 10px;">
                </span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg">
                    <img src="{{asset('images/invest-logo.png')}}" height="30px" width="30px" style="margin-top: 6px; margin-bottom: 10px;">
                    <b>NAVS</b>
                    INVEST
                </span>
            </a>

            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>

                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        @if(Auth::check())
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="https://res.cloudinary.com/dtlp5ycep/image/upload/v1528504342/user-icon.png" class="user-image" alt="User Image">
                                    <span class="hidden-xs">{{Auth::user()->name}}</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <img src="{{asset('images/user.png')}}" class="img-circle" alt="User Image">

                                        <p>
                                            {{Auth::user()->name}}
                                            <?php $since = date('M. Y', strtotime(Auth::user()->created_at))?>
                                            <small>Member since {{$since}}</small>
                                        </p>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="{{url('user-profile')}}" class="btn btn-default btn-flat">
                                                <i class="fa fa-user"></i> Profile
                                            </a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="{{ route('logout') }}" class="btn btn-default btn-flat"
                                               onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                                <i class="fa fa-sign-out fa-fw"></i> Log Out
                                            </a>

                                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                @csrf
                                            </form>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </nav>
        </header>
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <ul class="sidebar-menu" data-widget="tree">
                    {{--<li class="header" style="color:#ffffff;background-color: #222d32;text-align: center; border-right: 10px solid #367fa9; border-bottom: 10px solid #367fa9; border-left: 10px solid #367fa9;">--}}
                        {{--<img src="{{asset('images/invest-logo.png')}}" height="50px" width="50px" style="margin-top: 10px; margin-bottom: 10px; margin-right: 5px">--}}
                        {{--<b style="font-size: 25px">INVEST</b>--}}
                    {{--</li>--}}
                    <li class="header">MAIN MENU</li>
                    <li class="{{ Request::is('/') || Request::is('home') ? 'active' : '' }}"><a href="{{url('')}}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                    <li class="treeview {{ Request::is('*import*') ? 'active' : '' }}">
                        <a href="#">
                            <i class="glyphicon glyphicon-import"></i>
                            <span>Import Data</span>
                            <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('import-flightdetails') ? 'active' : '' }}"><a href="{{url('import-flightdetails')}}"><i class="fa fa-circle-o"></i> Flight Details</a></li>
                            <li class="{{ Request::is('import-invoices') ? 'active' : '' }}"><a href="{{url('import-invoices')}}"><i class="fa fa-circle-o"></i> Invoices</a></li>
                            <li class="{{ Request::is('import-addinvoices') ? 'active' : '' }}"><a href="{{url('import-addinvoices')}}"><i class="fa fa-circle-o"></i> Additional Invoices</a></li>
                        </ul>
                    </li>
                    <li class="treeview {{ Request::is('*charge*') ? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-plane"></i>
                            <span>Charge Category</span>
                            <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('charge-rc') ? 'active' : '' }}"><a href="{{url('charge-rc')}}"><i class="fa fa-circle-o"></i> Route Charge</a></li>
                            <li class="{{ Request::is('charge-tnc') ? 'active' : '' }}"><a href="{{url('charge-tnc')}}"><i class="fa fa-circle-o"></i> Terminal Navigation Charge</a></li>
                            <li class="{{ Request::is('charge-ovc') ? 'active' : '' }}"><a href="{{url('charge-ovc')}}"><i class="fa fa-circle-o"></i> Overflying Charge</a></li>
                        </ul>
                    </li>
                    <li class="treeview {{ Request::is('*unbilled*') ? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-money"></i>
                            <span>Unbilled Flight</span>
                            <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('unbilled-rc') ? 'active' : '' }}"><a href="{{url('unbilled-rc')}}"><i class="fa fa-circle-o"></i> Route Charge</a></li>
                            <li class="{{ Request::is('unbilled-tnc') ? 'active' : '' }}"><a href="{{url('unbilled-tnc')}}"><i class="fa fa-circle-o"></i> Terminal Navigation Charge</a></li>
                            <li class="{{ Request::is('unbilled-ovc') ? 'active' : '' }}"><a href="{{url('unbilled-ovc')}}"><i class="fa fa-circle-o"></i> Overflying Charge</a></li>
                        </ul>
                    </li>
                    <li class="header">DATA AND FILE MANAGEMENT</li>
                    <li class="treeview {{ Request::is('*manage*') ? 'active' : '' }}">
                        <a href="#">
                            <i class="fa fa-database"></i>
                            <span>Manage Database</span>
                            <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{ Request::is('manage-airports') ? 'active' : '' }}">
                                <a href="{{url('manage-airports')}}"><i class="fa fa-circle-o"></i> Airports</a>
                            </li>
                            <li class="{{ Request::is('manage-actypes') ? 'active' : '' }}">
                                <a href="{{url('manage-actypes')}}"><i class="fa fa-circle-o"></i> Aircraft Types</a>
                            </li>
                            <li class="{{ Request::is('manage-hajjflights') ? 'active' : '' }}">
                                <a href="{{url('manage-hajjflights')}}"><i class="fa fa-circle-o"></i> Hajj Flights</a>
                            </li>
                            <li class="{{ Request::is('manage-routeunits') ? 'active' : '' }}">
                                <a href="{{url('manage-routeunits')}}"><i class="fa fa-circle-o"></i> Route Units</a>
                            </li>
                            <li class="{{ Request::is('manage-invoices') ? 'active' : '' }}">
                                <a href="{{url('manage-invoices')}}"><i class="fa fa-circle-o"></i> Invoices</a>
                            </li>
                            <li class="{{ Request::is('manage-flightdetails') ? 'active' : '' }}">
                                <a href="{{url('manage-flightdetails')}}"><i class="fa fa-circle-o"></i> Flight Details</a>
                            </li>
                        </ul>
                    </li>
                    <li class="{{ Request::is('document') ? 'active' : '' }}">
                        <a href="{{url('document')}}"><i class="fa fa-book"></i> <span>Document</span></a>
                    </li>

                    <li style="text-align: center">

                    </li>
                </ul>
            </section>

            <div class="user-panel">

            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    @yield('page_heading')
                </h1>

                @yield('breadcrumb')
            </section>

            <!-- Main content -->
            @yield('section')

        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <div class="pull-right hidden-xs">
                <img src="{{asset('images/ga-skyteam.png')}}" height="25px" width="130px">

            </div>

            <strong>Copyright © 2018 <a href="https://github.com/kirandz/invoice-verify" target="_blank">Irfan Hafid</a> - JKTOSN</strong>
            <b style="margin-left: 10px">v</b>1.02

        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Create the tabs -->
            <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
                <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
                <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                <!-- Home tab content -->
                <div class="tab-pane" id="control-sidebar-home-tab">
                    <h3 class="control-sidebar-heading">Recent Activity</h3>
                    <ul class="control-sidebar-menu">
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-birthday-cake bg-red"></i>

                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                                    <p>Will be 23 on April 24th</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-user bg-yellow"></i>

                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>

                                    <p>New phone +1(800)555-1234</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>

                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>

                                    <p>nora@example.com</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-file-code-o bg-green"></i>

                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>

                                    <p>Execution time 5 seconds</p>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <!-- /.control-sidebar-menu -->

                    <h3 class="control-sidebar-heading">Tasks Progress</h3>
                    <ul class="control-sidebar-menu">
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Custom Template Design
                                    <span class="label label-danger pull-right">70%</span>
                                </h4>

                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Update Resume
                                    <span class="label label-success pull-right">95%</span>
                                </h4>

                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-success" style="width: 95%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Laravel Integration
                                    <span class="label label-warning pull-right">50%</span>
                                </h4>

                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Back End Framework
                                    <span class="label label-primary pull-right">68%</span>
                                </h4>

                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <!-- /.control-sidebar-menu -->

                </div>
                <!-- /.tab-pane -->
                <!-- Stats tab content -->
                <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
                <!-- /.tab-pane -->
                <!-- Settings tab content -->
                <div class="tab-pane" id="control-sidebar-settings-tab">
                    <form method="post">
                        <h3 class="control-sidebar-heading">General Settings</h3>

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Report panel usage
                                <input type="checkbox" class="pull-right" checked>
                            </label>

                            <p>
                                Some information about this general settings option
                            </p>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Allow mail redirect
                                <input type="checkbox" class="pull-right" checked>
                            </label>

                            <p>
                                Other sets of options are available
                            </p>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Expose author name in posts
                                <input type="checkbox" class="pull-right" checked>
                            </label>

                            <p>
                                Allow the user to show his name in blog posts
                            </p>
                        </div>
                        <!-- /.form-group -->

                        <h3 class="control-sidebar-heading">Chat Settings</h3>

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Show me as online
                                <input type="checkbox" class="pull-right" checked>
                            </label>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Turn off notifications
                                <input type="checkbox" class="pull-right">
                            </label>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Delete chat history
                                <a href="javascript:void(0)" class="text-red pull-right"><i class="fa fa-trash-o"></i></a>
                            </label>
                        </div>
                        <!-- /.form-group -->
                    </form>
                </div>
                <!-- /.tab-pane -->
            </div>
        </aside>
        <!-- /.control-sidebar -->
        <!-- Add the sidebar's background. This div must be placed
             immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    <
