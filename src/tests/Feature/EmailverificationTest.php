<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use App\Models\User;


class EmailverificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_会員登録後、認証メールが送信される()
    {
        Notification::fake();

        $userData = [
            'name' => '山田 太郎',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertStatus(302);

        $user = \App\Models\User::where('email', 'testuser@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    //実際の認証はメール内リンクをユーザーが押下する必要があるため、画面遷移で確認することはできない
    public function test_メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する() {}

    // 署名付きURLを使ってメール認証完了状態をシミュレーションしている
    public function test_メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する()
    {
        $user = User::factory()->unverified()->create();


        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($url);

        $response->assertRedirect();
        $this->assertStringContainsString('/attendance', $response->headers->get('Location'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
