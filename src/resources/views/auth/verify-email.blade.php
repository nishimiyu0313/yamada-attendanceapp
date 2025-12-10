<p>
    ご登録ありがとうございます
</p>

@if (session('status') == 'verification-link-sent')
<div>
    認証リンクがご登録のメールアドレスに送信されました。
</div>
@endif

<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <div>
        <button type="submit">認証メールを再送信</button>
    </div>
</form>