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
            $role = Auth::guard('employees')->user()->role;
            $route = match($role) {
                'manager'     => 'employee.orders.index',
                'surveyor'    => 'employee.measurements.index',
                'constructor' => 'employee.documentations.index',
                'installer'   => 'employee.installations.index',
                default       => 'employee.orders.index',
            };
            return redirect()->route($route);
        }
        return redirect()->route('login')->withErrors('Неверный email или пароль');
    }
}
