<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="overflow-y: auto !important;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>
    <link rel="icon" href="{!! asset('./img/bot.jpeg')!!}" />

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
    <div id="app">
        <!-- <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm" > -->
        <div class="container">
            <a class="navbar-brand" href="{{ route('login') }}">
                <img src="{!! asset('./img/bot.jpeg')!!}" alt="Bot" style="height: 90px; width: 90px;">
                {{ config('app.name') }}
            </a>
            <!-- <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    Left Side Of Navbar
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    Right Side Of Navbar
                    <ul class="navbar-nav ml-auto">
                        Authentication Links
                        @guest
                            @if (Route::is('register'))
                                <li class="nav-item">
                                    <a class="btn btn-outline-success" style="margin: 5px;" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                            @if (Route::has('register') && Route::is('login'))
                                <li class="nav-item">
                                    <a class="btn btn-outline-info" style="margin: 5px;" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <button id="navbarDropdown" class="btn bg-secondary dropdown-toggle" style="color: white;" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->short_name }}
                                </button>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="
                                                event.preventDefault();
                                                document.getElementById('logout-form').submit();
                                                "
                                    >
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul> -->
            <!-- </div> -->
        </div>
        <!-- </nav> -->

        <main class="py-4">
            <router-view></router-view>
            @yield('content')
        </main>
    </div>
</body>

</html>

<style scoped="scss">
body {
    height: 100vh;
    background: -webkit-linear-gradient(bottom, #003, #ffd);
    /* For Safari 5.1 to 6.0 */
    background: -o-linear-gradient(bottom, #003, #ffd);
    /* For Opera 11.1 to 12.0 */
    background: -moz-linear-gradient(bottom, #003, #ffd);
    /* For Firefox 3.6 to 15 */
    background: linear-gradient(to bottom, #003, #ffd);
    /* Standard syntax */
}

html {
    overflow: hidden !important;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

html::-webkit-scrollbar {
    width: 0;
    height: 0;
}
</style>