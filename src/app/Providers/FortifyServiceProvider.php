<?php

namespace App\Providers;

use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;







class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });
        Fortify::loginView(function () {
            return view('auth.login');
        });

        //$this->app->bind(\Laravel\Fortify\Http\Requests\LoginRequest::class, LoginRequest::class);

        Fortify::authenticateUsing(function (Request $request) {

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['ログイン情報が一致しません'],
                ]);
            }

            // もし admin/login からのリクエストなら role チェック
            if ($request->routeIs('admin.login.post')) {
                if ($user->role !== 'admin') {
                    throw ValidationException::withMessages([
                        'email' => ['管理者のみログインできます'],
                    ]);
                }
            }

            if ($request->routeIs('login')) { // Fortify 標準 login route
                if ($user->role !== 'user') {
                    throw ValidationException::withMessages([
                        'email' => ['ユーザーのみログインできます'],
                    ]);
                }
            }

            return $user;
        });




        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Actions\Fortify\AdminLoginResponse::class
        );
    }
}
