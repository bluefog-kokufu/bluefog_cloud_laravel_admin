<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bluefog Cloud 管理画面</title>
    @vite(['resources/css/app.css'])
</head>

<body>

    <div id="loginView">
        <div class="login-card">
            <h1>Bluefog Cloud</h1>
            <div class="sub">管理画面ログイン</div>

            <form method="POST" action="{{ route('admin.login.attempt') }}">
                @csrf
                <label for="email">メールアドレス</label>
                <input id="loginEmail" type="email" name="email" value="{{ old('email') }}" autofocus autocomplete="username">

                <label for="password">パスワード</label>
                <input id="loginPw" type="password" name="password" autocomplete="current-password">

                @if ($errors->any())
                <div class="err">{{ $errors->first() }}</div>
                @endif

                <button class="btn block" type="submit">ログイン</button>
            </form>
        </div>
    </div>

    @vite(['resources/js/app.js'])

</body>

</html>
