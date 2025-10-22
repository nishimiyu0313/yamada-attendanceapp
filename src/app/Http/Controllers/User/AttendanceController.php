<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller {

public function  attendance()
    {
        return view('user.registration');
    }

    public function  list()
    {
        return view('user.list');
    }

    public function  request()
    {
        return view('user.request');
    }

}