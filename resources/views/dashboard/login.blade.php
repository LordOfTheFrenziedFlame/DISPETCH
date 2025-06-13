<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админку</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <link href="{{ asset('vendor/tabler/dashboard.css') }}" rel="stylesheet" />
    <link href={{ asset('vendor/fontawesome/css/fontawesome.css') }} rel="stylesheet">
</head>
<body class="">
<div class="page">
    <div class="page-single">
        <div class="container">
            <div class="row">
                <div class="col col-login mx-auto">

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login') }}" class="card">
                        @csrf
                        <div class="card-body p-6">
                            <div class="card-title">Вход в админку</div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email"
                                       name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}"
                                       placeholder="Введите email">
                                @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <input type="password"
                                       name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Введите пароль">
                                @error('password')
                                <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary btn-block">Войти</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Скрипты --}}
<script src="{{ asset('vendor/js_for_dashboard/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/js_for_dashboard/bootstrap.min.js') }}"></script>
<script> feather.replace(); </script>
</body>
</html>
