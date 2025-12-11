<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserregisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_名前が未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');

        $errors = session('errors');
        $this->assertEquals('お名前を入力してください', $errors->first('name'));
    }

    public function test_メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => '山田 太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    public function  test_パスワードが8文字未満の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }

    public function test_パスワードが一致しない場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1236',
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードと一致しません', $errors->first('password'));
    }

    public function test_パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name' => '山田 太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    public function test_フォームに内容が入力されていた場合、データが正常に保存される()
    {
        $userData = [
            'name' => '山田 太郎',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);


        $this->assertDatabaseHas(
            'users',
            [
                'email' => 'testuser@example.com',
                'name' => '山田 太郎',
            ]
        );

    }
}
