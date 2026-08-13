<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bluefog Cloud 管理画面</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div id="app">
        <header class="topbar">
            <div class="brand"><span class="mark"></span>Bluefog Cloud 管理画面</div>
            <div class="userbox">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn ghost small">ログアウト</button>
                </form>
            </div>
        </header>
        <div class="layout">
            <nav class="side">
                <div class="navttl">MENU</div>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">ホーム</a>
                <a href="{{ route('admin.notices.index') }}" class="{{ request()->routeIs('admin.notices.*') ? 'active' : '' }}">お知らせ配信管理</a>
                <div class="navttl">今後実装予定</div>
                <a class="disabled">契約企業管理</a>
                <a class="disabled">スタッフアカウント管理</a>
            </nav>
            <main id="page">
                @yield('content')
            </main>
        </div>
    </div>

    @vite(['resources/js/app.js'])
</body>

</html>
