<ul class="header-nav__list">

    <li class="header-nav__item">
        <a class="header-nav__link" href="{{ route('') }}">勤怠</a>
    </li>

    <li class="header-nav__item">
        <a class="header-nav__link" href="{{ route('') }}">勤怠一覧</a>
    </li>

    <li class="header-nav__item">
        <a class="header-nav__link" href="{{ route('') }}">申請</a>
    </li>

    <li class="header-nav__item">
        <form action="{{ route('logout') }}" method="post">
        @csrf
        <button class="header-nav__logout" type="submit">ログアウト</button>
        </form>
    </li>

</ul>
