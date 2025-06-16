<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class UsersController extends Controller
{
    public function index()
    {
        if(Auth::guard('employees')->user()->role !== 'manager') {
            return redirect()->back()->withErrors('У вас нет доступа к просмотру пользователей');
        }
        $users = User::all();
        return view('dashboard.users', compact('users'));
    }

    public function profile()
    {
        $user = Auth::guard('employees')->user();
        return view('dashboard.profile', compact('user'));
    }

    public function userAdd(Request $request)
    {
        if(Auth::guard('employees')->user()->role !== 'manager') {
            return redirect()->back()->withErrors('У вас нет доступа к добавлению пользователей');
        }

        $validate = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-Я\s]+$/u',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:manager,surveyor,constructor,installer',
        ], [
            'name.regex' => 'Имя может содержать только буквы и пробелы'
        ]);

        User::create($validate);
        return redirect()->back()->with('success', 'Пользователь успешно добавлен');
    }

    public function changeRole(Request $request, User $user)
    {
        $auth = Auth::guard('employees')->user();

        // Менеджер не может изменить свою роль
        if ($auth->id === $user->id) {
            return back()->withErrors('Вы не можете изменить свою собственную роль');
        }

        // Только менеджер может изменять других
        if ($auth->role !== 'manager') {
            return back()->withErrors('У вас нет прав на изменение роли пользователя');
        }

        $validated = $request->validate([
            'role' => 'required|string|in:manager,surveyor,constructor,installer',
        ]);

        $user->update($validated);

        return back()->with('success', 'Роль пользователя успешно изменена');
    }

    public function userDelete(User $user)
    {
        $auth = Auth::guard('employees')->user();

        // Менеджер не может удалить сам себя
        if ($auth->id === $user->id) {
            return back()->withErrors('Вы не можете удалить свою собственную учётную запись');
        }

        // Только менеджер может удалять
        if ($auth->role !== 'manager') {
            return back()->withErrors('У вас нет прав на удаление пользователя');
        }

        $user->delete();

        return back()->with('success', 'Пользователь успешно удален');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-Я\s]+$/u',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::guard('employees')->user()->id,
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'name.regex' => 'Имя может содержать только буквы и пробелы'
        ]);

        $user = Auth::guard('employees')->user();
        $user->name = $request->name;
        $user->email = $request->email;
        if($request->password) {
            $user->password = $request->password;
        }
        $user->save();

        return redirect()->back()->with('success', 'Профиль успешно обновлен');
        
    }
}
