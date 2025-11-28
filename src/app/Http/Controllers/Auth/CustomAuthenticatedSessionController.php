<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomAuthenticatedSessionController extends Controller
{
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->routeIs('admin.*')) {
            return redirect('/admin/login');
        }
        // ログアウト後に飛ばしたいページ
        return redirect('/login');  // ここを好きなURLに変更
    }
}
