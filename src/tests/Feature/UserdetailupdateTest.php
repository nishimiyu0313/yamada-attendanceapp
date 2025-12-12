<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserdetailupdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される() {}

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される() {}

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される() {}

    public function test_備考欄が未入力の場合のエラーメッセージが表示される() {}

    public function test_修正申請処理が実行される() {}

    public function test_「承認待ち」にログインユーザーが行った申請が全て表示されていること() {}

    public function test_「承認済み」に管理者が承認した修正申請が全て表示されている() {}

    public function test_各申請の「詳細」を押下すると勤怠詳細画面に遷移する() {}
}
