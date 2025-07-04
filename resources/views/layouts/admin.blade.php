<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Админка')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <link href="{{ asset('vendor/tabler/dashboard.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/fontawesome.css') }}">
    <script src="{{ asset('vendor/feather-icons/dist/feather.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('vendor/calendar/fullcalendar.global.min.css') }}">
</head>
<body>

<div class="page">
    <div class="page-main">

        {{-- Шапка --}}
        <div class="header py-4">
            <div class="container">
                <div class="d-flex">
                    <a class="header-brand" href="#">
                        <span class="header-brand-text">Админка</span>
                    </a>

                    <div class="d-flex order-lg-2 ml-auto">
                        {{-- Колокольчик --}}
                        {{-- @php
                            $notifications = auth()->user()->unreadNotifications;
                        @endphp --}}

                        {{-- <div class="dropdown d-none d-md-flex">
                            <a class="nav-link icon" data-toggle="dropdown">
                                <i data-feather="bell"></i>
                                @if($notifications->count())
                                    <span class="nav-unread"></span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                @forelse($notifications as $notification)
                                    <a href="{{ $notification->data['link'] ?? '#' }}" class="dropdown-item d-flex">
                                        <span class="avatar mr-3 align-self-center" style="background-image: url('/demo/faces/male/41.jpg')"></span>
                                        <div>
                                            <strong>{{ $notification->data['title'] }}</strong>
                                            <div class="text-muted small">{{ $notification->data['message'] }}</div>
                                            <div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="dropdown-item text-center text-muted">Нет уведомлений</div>
                                @endforelse

                                <div class="dropdown-divider"></div>
                                <form action="#" method="POST">
                                    @csrf
                                    <button class="dropdown-item text-center text-muted-dark">Отметить все как прочитанные</button>
                                </form>
                            </div>
                        </div> --}}

                        {{-- Профиль --}}
                        <div class="dropdown">
                            <a href="#" class="nav-link pr-0 leading-none" data-toggle="dropdown">
                                <span class="avatar" style="background-image: url('/demo/faces/female/25.jpg')"></span>
                                <span class="ml-2 d-none d-lg-block">
                                    <span class="text-default">{{auth('employees')->user()->name ?? 'Админка'}}</span>
                                    <small class="text-muted d-block mt-1">{{auth('employees')->user()->role ?? 'Админка'}}</small>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('employee.users.profile') }}"><i class="dropdown-icon fe fe-user"></i> Профиль</a>
                                <a class="dropdown-item" href="{{ route('logout') }}"><i class="dropdown-icon fe fe-log-out"></i> Выйти</a>
                            </div>
                        </div>
                    </div>

                    <a href="#" class="header-toggler d-lg-none ml-3 ml-lg-0" data-toggle="collapse" data-target="#headerMenuCollapse">
                        <span class="header-toggler-icon"></span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Горизонтальное меню --}}
        <div class="header collapse d-lg-flex p-0" id="headerMenuCollapse">
            <div class="container">
                <div class="row align-items-center w-100">
                    <div class="col">
                        @php $role = auth('employees')->user()->role; @endphp
                        <ul class="nav nav-tabs border-0 flex-row">
                            @if($role === 'manager')
                                {{-- Менеджер видит всё --}}
                                <li class="nav-item"><a href="{{ route('employee.orders.index') }}" class="nav-link {{ request()->routeIs('employee.orders.*') ? 'active' : '' }}"><i class="fe fe-file-text"></i> Заявки</a></li>
                                <li class="nav-item"><a href="{{ route('employee.measurements.index') }}" class="nav-link {{ request()->routeIs('employee.measurements.*') ? 'active' : '' }}"><i class="fe fe-ruler"></i> Замеры</a></li>
                                <li class="nav-item"><a href="{{ route('employee.contracts.index') }}" class="nav-link {{ request()->routeIs('employee.contracts.*') ? 'active' : '' }}"><i class="fe fe-book"></i> Договоры</a></li>
                                <li class="nav-item"><a href="{{ route('employee.documentations.index') }}" class="nav-link {{ request()->routeIs('employee.documentations.*') ? 'active' : '' }}"><i class="fe fe-book"></i> Документация</a></li>
                                <li class="nav-item"><a href="{{ route('employee.productions.index') }}" class="nav-link {{ request()->routeIs('employee.productions.*') ? 'active' : '' }}"><i class="fe fe-box"></i> Производство</a></li>
                                <li class="nav-item"><a href="{{ route('employee.installations.index') }}" class="nav-link {{ request()->routeIs('employee.installations.*') ? 'active' : '' }}"><i class="fe fe-tool"></i> Установка</a></li>
                                <li class="nav-item"><a href="{{ route('employee.users.index') }}" class="nav-link {{ request()->routeIs('employee.users.*') ? 'active' : '' }}"><i class="fe fe-users"></i> Пользователи</a></li>
                                <li class="nav-item"><a href="{{ route('employee.archived.index') }}" class="nav-link {{ request()->routeIs('employee.archived.*') ? 'active' : '' }}"><i class="fe fe-settings"></i> Архив</a></li>
                            @elseif($role === 'surveyor')
                                <li class="nav-item"><a href="{{ route('employee.measurements.index') }}" class="nav-link {{ request()->routeIs('employee.measurements.*') ? 'active' : '' }}"><i class="fe fe-ruler"></i> Замеры</a></li>
                                <li class="nav-item"><a href="{{ route('employee.documentations.index') }}" class="nav-link {{ request()->routeIs('employee.documentations.*') ? 'active' : '' }}"><i class="fe fe-book"></i> Документация</a></li>
                            @elseif($role === 'constructor')
                                <li class="nav-item"><a href="{{ route('employee.orders.index') }}" class="nav-link {{ request()->routeIs('employee.orders.*') ? 'active' : '' }}"><i class="fe fe-file-text"></i> Заявки</a></li>
                                <li class="nav-item"><a href="{{ route('employee.measurements.index') }}" class="nav-link {{ request()->routeIs('employee.measurements.*') ? 'active' : '' }}"><i class="fe fe-ruler"></i> Замеры</a></li>
                                <li class="nav-item"><a href="{{ route('employee.documentations.index') }}" class="nav-link {{ request()->routeIs('employee.documentations.*') ? 'active' : '' }}"><i class="fe fe-book"></i> Документация</a></li>
                                <li class="nav-item"><a href="{{ route('employee.productions.index') }}" class="nav-link {{ request()->routeIs('employee.productions.*') ? 'active' : '' }}"><i class="fe fe-box"></i> Производство</a></li>
                                <li class="nav-item"><a href="{{ route('employee.installations.index') }}" class="nav-link {{ request()->routeIs('employee.installations.*') ? 'active' : '' }}"><i class="fe fe-tool"></i> Установка</a></li>
                            @elseif($role === 'installer')
                                <li class="nav-item"><a href="{{ route('employee.installations.index') }}" class="nav-link {{ request()->routeIs('employee.installations.*') ? 'active' : '' }}"><i class="fe fe-tool"></i> Установка</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Контент --}}
        <div class="content px-4 py-2 w-100">
            {{-- Сообщения об ошибках и успехе --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    {{ session('info') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    {{-- Подвал --}}
    <footer class="footer">
        <div class="container text-center">
            <p class="mb-0 text-muted">© {{ date('Y') }} Buisness Helper</p>
        </div>
    </footer>
</div>

{{-- Скрипты --}}
<script src="{{ asset('vendor/js_for_dashboard/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/js_for_dashboard/popper.js') }}"></script>
<script src="{{ asset('vendor/js_for_dashboard/bootstrap.min.js') }}"></script>

<script>
    // Инициализация Feather Icons и Bootstrap dropdown
    $(function () {
        feather.replace();
        $('.dropdown-toggle').dropdown();
    });
</script>
</body>
</html>
