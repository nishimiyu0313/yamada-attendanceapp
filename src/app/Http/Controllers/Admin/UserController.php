<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;


class UserController extends Controller
{
    public function index(Request $request) {
        $users = User::where('role', 'user')
            ->orderBy('name')
            ->get();

        return view('admin.stafflist', compact('users'));

    }
}
