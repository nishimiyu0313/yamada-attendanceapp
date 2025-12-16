<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech</title>
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
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
            <div class="verify-form__content">

                <p>
                    登録していただいたメールアドレスに認証メールを送付しました。<br>
                    メール認証を完了してください。
                </p>

                @if (session('status') == 'verification-link-sent')
                <div>
                    認証リンクがご登録のメールアドレスに送信されました。
                </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="resend-button">
                        認証メールを再送する
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>

</html>