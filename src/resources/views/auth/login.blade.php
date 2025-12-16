<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css')}}">
</head>

<body>
    <div class="app">
        <header class="header">
            <a class="header__heading">
                <img src="{{ asset('images/logo.pmg1.svg') }}" alt="COACHTECHロゴ">
            </a>
        </header>
        <main>
            <div class="login-form__content">
                <div class="login-form__heading">
                    <h2>ログイン</h2>
                </div>

                <form class="form" action="/login" method="post" novalidate>
                    @csrf
                    <div class="form__group">
                        <div class="form__group-title">
                            <span class="form__label--item">メールアドレス</span>
                        </div>
                        <div class="form__group-content">
                            <div class="form__input--text">
                                <input type="email" name="email" value="{{ old('email') }}" />
                            </div>
                            <div class="form__error">
                                @error('email')
                                <div class="text-danger" style="font-size: 0.9em; margin-top: 4px;">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form__group">
                        <div class="form__group-title">
                            <span class="form__label--item">パスワード</span>
                        </div>
                        <div class="form__group-content">
                            <div class="form__input--text">
                                <input type="password" name="password" />
                            </div>
                            <div class="form__error">
                                @error('password')
                                <div class="text-danger" style="font-size: 0.9em; margin-top: 4px;">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form__button">
                        <button class="form__button-submit" type="submit">ログインする</button>
                    </div>
                </form>
                <div class="register__link">
                    <a class="register__button-submit" href="/register">会員登録はこちら</a>
                </div>
            </div>
        </main>
    </div>
</body>

</html>