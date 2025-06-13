@extends('layouts.admin')

@section('title', 'Профиль')

@section('content')
    <form action="{{ route('employee.users.updateProfile') }}" method="POST" class="card">
        @csrf
        <div class="card-header">
            <h3 class="card-title">Редактировать профиль</h3>
        </div>

        <div class="card-body">
            {{-- Уведомления --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Имя --}}
            <div class="form-group mb-3">
                <label for="name" class="form-label">Имя</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="{{ old('name', auth()->user()->name) }}" required>
            </div>

            {{-- Email --}}
            <div class="form-group mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="{{ old('email', auth()->user()->email) }}" required>
            </div>

            {{-- Новый пароль --}}
            <div class="form-group mb-3">
                <label for="password" class="form-label">Новый пароль</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Оставьте пустым, если не меняете">
            </div>

            {{-- Подтверждение --}}
            <div class="form-group mb-3">
                <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
            </div>
        </div>

        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
        </div>
    </form>
@endsection
