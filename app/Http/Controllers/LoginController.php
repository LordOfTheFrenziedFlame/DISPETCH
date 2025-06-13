<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LoginController extends Controller
{
    public function login()
    {
        return view('dashboard.login');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::guard('employees')->attempt($credentials)) {
            return redirect()->route('employee.orders.index');
        }
        return redirect()->route('login')->withErrors('Неверный email или пароль');
    }
}
