<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminattendanceupdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_承認待ちの修正申請が全て表示されている() {}

    public function test_承認済みの修正申請が全て表示されている() {}

    public function test_修正申請の詳細内容が正しく表示されている() {}

    public function test_修正申請の承認処理が正しく行われる() {}
}
