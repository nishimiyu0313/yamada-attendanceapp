<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdmindetailupdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている() {}

    public function test_出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される() {}

    public function test_休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される() {}

    public function test_休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される() {}

    public function test_備考欄が未入力の場合のエラーメッセージが表示される() {}
}
