@extends('layouts.admin')

@section('title', 'Пользователи')

@section('content')
    <div class="row">
        {{-- Форма добавления пользователя --}}
        <div class="col-lg-5">
            <form method="POST" action="{{ route('employee.users.userAdd') }}" class="card">
                @csrf
                <div class="card-header">
                    <h3 class="card-title">Добавить пользователя</h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group mb-3">
                        <label class="form-label">Имя</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Подтверждение пароля</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Роль</label>
                        <select name="role" class="form-select">
                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Менеджер</option>
                            <option value="surveyor" {{ old('role') == 'surveyor' ? 'selected' : '' }}>Замерщик</option>
                            <option value="constructor" {{ old('role') == 'constructor' ? 'selected' : '' }}>Конструктор</option>
                            <option value="installer" {{ old('role') == 'installer' ? 'selected' : '' }}>Установщик</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>

        {{-- Список пользователей --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Список пользователей</h3></div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Роль</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('employee.users.changeRole', ['user' => $user]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>manager</option>
                                                <option value="surveyor" {{ $user->role === 'surveyor' ? 'selected' : '' }}>surveyor</option>
                                                <option value="constructor" {{ $user->role === 'constructor' ? 'selected' : '' }}>constructor</option>
                                                <option value="installer" {{ $user->role === 'installer' ? 'selected' : '' }}>installer</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        @if (auth()->user()->id !== $user->id)
                                            <form method="POST" action="{{ route('employee.users.userDelete', ['user' => $user]) }}"
                                                  onsubmit="return confirm('Удалить пользователя?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted text-center">Нет пользователей</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
