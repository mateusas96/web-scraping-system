<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>{{ config('app.name') }}</title>
    <link rel="icon" href="{!! asset('./img/bot.jpeg')!!}" />

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="{{ mix('css/main.css') }}" rel="stylesheet">

</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper" id="app">
        <!-- <v-app data-app> -->
            <!-- Navbar -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu"><i class="fas fa-bars"></i></a>
                    </li>
                    <li class="nav-item d-none d-sm-inline-block">
                        <router-link to="/home" class="nav-link" style="background-color: #ffffff !important;">
                            {{__('Home')}}</roter-link>
                    </li>
                    <!-- <li class="nav-item d-none d-sm-inline-block">
                        <a href="#" class="nav-link">Contact</a>
                    </li> -->
                </ul>

                <!-- SEARCH FORM -->
                <form class="form-inline ml-3">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" placeholder="{{__('Search')}}"
                            aria-label="{{__('Search')}}">
                        <div class="input-group-append">
                            <button class="btn btn-navbar" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </nav>
            <!-- /.navbar -->

            <!-- Main Sidebar Container -->
            <aside class="main-sidebar sidebar-dark-primary elevation-4">
                <!-- Brand Logo -->
                <router-link to="/home" class="brand-link"
                    style="background-color: #343a40 !important; color: #C2C7D0 !important;">
                    <img src="{!! asset('./img/bot.jpeg')!!}" alt="WSS Logo" class="brand-image img-circle elevation-3"
                        style="opacity: .8; margin-left: 0px">
                    <span class="brand-text font-weight-light">{{ config('app.name') }}</span>
                </router-link>

                <!-- Sidebar -->
                <div class="sidebar"  style="margin-left: -3px">
                    <!-- Sidebar user panel (optional) -->
                    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                        <div class="image">
                            <img src="{!! asset('./img/user.png')!!}" class="img-circle elevation-2" alt="User Image">
                        </div>
                        <div class="info">
                            <a class="d-block" style="color: #C2C7D0 !important;">Hello, {{Auth::user()->first_name}}!</a>
                        </div>
                    </div>

                    <!-- Sidebar Menu -->
                    <nav class="mt-2">
                        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                            data-accordion="false">
                            <!-- Add icons to the links using the .nav-icon class
                                with font-awesome or any other icon font library -->
                            @if(Auth::user()->is_admin === 1)
                                <li class="nav-item has-treeview menu-close">
                                    <a class="nav-link">
                                        <i class="nav-icon fas fa-crown teal"></i>
                                        <p>
                                            {{__('Administration')}}
                                            <i class="right fas fa-angle-left teal"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <router-link to="/users/management" class="nav-link">
                                                <i class="nav-icon fas fa-users-cog green"></i>
                                                <p>{{__('Users management')}}</p>
                                            </router-link>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                            <li class="nav-item">
                                <router-link to="/upload-configs" class="nav-link">
                                    <i class="nav-icon fas fa-file-upload orange"></i>
                                    <p>{{__('Scraping config upload')}}</p>
                                </router-link>
                            </li>
                            <li class="nav-item">
                                <router-link to="/scrape-data" class="nav-link">
                                    <i class="nav-icon fas fa-spider pink"></i>
                                    <p>{{__('Scrape data')}}</p>
                                </router-link>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn" href="{{ route('logout') }}" 
                                    onclick="
                                        event.preventDefault();
                                        document.getElementById('logout-form').submit();
                                    "
                                >
                                    <i class="fas fa-sign-out-alt"></i>
                                    <p>{{ __('Logout') }}</p>
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </nav>
                    <!-- /.sidebar-menu -->
                </div>
                <!-- /.sidebar -->
            </aside>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                @if (session('message'))
                    <div class="alert alert-danger">{{ session('message') }}</div>
                @endif
            <!-- Content Header (Page header) -->
                <!-- <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Starter Page</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Starter Page</li>
                        </ol>
                    </div>
                    </div>
                </div>
                </div> -->
                <!-- /.content-header -->
                <v-app style="background-color: #f4f6f9 !important;">
                    <!-- Vue content -->
                    <main class="py-4">
                        <router-view></router-view>
                        <vue-progress-bar></vue-progress-bar>
                        @yield('content')
                    </main>
                </v-app>

            </div>
            <!-- /.content-wrapper -->

            <!-- Main Footer -->
            <footer class="main-footer">
                <!-- To the right -->
                <div class="float-right d-none d-sm-inline">
                    {{ __('Scrape anything you want') }}
                </div>
                <!-- Default to the left -->
                <strong>Copyright &copy; 2020 <a
                        href="{{ config('app.contentUrl') }}">{{ config('app.creator') }}</a>.</strong>
            </footer>
        <!-- </v-app> -->
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>

</body>

</html>