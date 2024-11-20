<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    //
    public function register(){

        return view('admin.auth.register');
    }

    public function login(){

        return view('admin.auth.login');
    }
}
