<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
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
            <div class="content">
                <div class="register-form__content">
                    <div class="register-form__heading">
                        <h2>会員登録</h2>
                    </div>
                    <form class="form" action="/register" method="post" novalidate>
                        @csrf
                        <div class="form__group">
                            <div class="form__group-title">
                                <span class="form__label--item">ユーザー名</span>
                            </div>
                            <div class="form__group-content">
                                <div class="form__input--text">
                                    <input type="text" name="name" value="{{ old('name') }}" />
                                </div>
                                <div class="form__error">
                                    @error('name')
                                    <div class="text-danger" style="font-size: 0.9em; margin-top: 4px;">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
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
                        <div class="form__group">
                            <div class="form__group-title">
                                <span class="form__label--item">確認用パスワード</span>
                            </div>
                            <div class="form__group-content">
                                <div class="form__input--text">
                                    <input type="password" name="password_confirmation" />
                                </div>
                            </div>
                        </div>
                        <div class="form__button">
                            <button class="form__button-submit" type="submit">登録する</button>
                        </div>
                    </form>
                    <div class="login__link">
                        <a class="login__button-submit" href="/login">ログインはこちら</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>