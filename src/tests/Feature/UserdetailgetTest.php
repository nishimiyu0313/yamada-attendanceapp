<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserdetailgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤怠詳細画面の「名前」がログインユーザーの氏名になっている() {}

    public function test_勤怠詳細画面の「日付」が選択した日付になっている() {}

    public function test_「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している() {}

    public function test_「休憩」にて記されている時間がログインユーザーの打刻と一致している() {}

}
