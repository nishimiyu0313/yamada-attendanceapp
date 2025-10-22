<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachteckattendance</title>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css')}}">
    @yield('css')
</head>

<body>
    <div class="app">
        <header class="header">
            <a class="header__heading" href="/">
                <img src="{{ asset('images/logo.pmg1.svg') }}" alt="COACHTECHロゴ">
            </a>

            <ul class="header-nav">
                <li class="header-nav__item">

                    <form class="attendance__form" action="/attendance" method="get" novalidate>
                        <button class="header-nav__button">勤怠</button>
                    </form>
                    <form class="list__form" action="/attendance/list" method="get" novalidate>
                        <button class="header-nav__button">勤怠一覧</button>
                    </form>
                    <form class="request__form" action="/stamp_correction_request/list" method="get" novalidate>
                        <button class="header-nav__button">申請</button>
                    </form>
                    @if (Auth::check())
                    <form class="form" action="/logout" method="post" novalidate>
                        @csrf
                        <button class="header-nav__button">ログアウト</button>
                    </form>
                    @endif

                </li>


            </ul>
        </header>
        <div class="content">
            @yield('content')
        </div>
    </div>










</body>

</html>