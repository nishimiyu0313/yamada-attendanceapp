<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class DategetTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在の日時情報がUIと同じ形式で出力されている()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        
        $now = Carbon::create(2025, 12, 11, 15, 30, 0); // 例: 2025-12-11 15:30:00
        Carbon::setTestNow($now);

        
        // 画面表示（ログインなど必要に応じて行う）
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        // UIと同じ形式で表示されているか確認
        // 例: '2025/12/11 15:30'
        
        $formattedDate = $now->format('Y年m月d日') . '（' . $weekdays[$now->dayOfWeek] . '）';
        $response->assertSeeText($formattedDate);

        $formattedTime = $now->format('H:i');
        $response->assertSeeText($formattedTime);
}
}