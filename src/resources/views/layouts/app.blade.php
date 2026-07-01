<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>

    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__logo">
            <a href="{{ View::yieldContent('logo_route', 'index') }}">
                <img class="header__logo-img" src="{{ asset('img/logo.png') }}" alt="ロゴ">
            </a>
        </div>
        <nav class="header__nav">
            @yield('nav')
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

</body>
</html>

