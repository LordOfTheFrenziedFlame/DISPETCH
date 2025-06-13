@extends('layouts.admin')

@vite(['resources/js/app.js'])

@section('title', 'Производство')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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

<div class="card my-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            Производство
            @if(request('manager_id'))
                @if($selectedEmployee)
                    <small class="text-muted">- {{ $selectedEmployee->name }}
                        @if($selectedEmployee->role === 'manager')
                            (Менеджер)
                        @elseif($selectedEmployee->role === 'surveyor')
                            (Замерщик)
                        @elseif($selectedEmployee->role === 'constructor')
                            (Конструктор)
                        @elseif($selectedEmployee->role === 'installer')
                            (Монтажник)
                        @endif
                    </small>
                @endif
            @endif
        </h3>
        <div class="btn-group">
            <a href="{{ route('employee.productions.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fe fe-rotate-ccw"></i> 
                @if(request('manager_id'))
                    Сбросить фильтр
                @else
                    Обновить
                @endif
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" id="sortEmployeeDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fe fe-user"></i> Фильтр по менеджерам
                </button>
                <div class="dropdown-menu" aria-labelledby="sortEmployeeDropdown">
                    @foreach($employees as $employee)
                        <a class="dropdown-item {{ request('manager_id') == $employee->id ? 'active' : '' }}" href="{{ route('employee.productions.index', ['manager_id' => $employee->id]) }}">
                            {{ $employee->name }} 
                            <small class="text-muted">(Менеджер)</small>
                            @if(request('manager_id') == $employee->id)
                                <i class="fe fe-check ms-2"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap table-productions">
            <thead>
                <tr>
                    <th>№ Заказа</th>
                    <th>Клиент</th>
                    <th>Адрес</th>
                    <th>Установщик</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productions as $production)
                    <tr>
                        <td>
                             <a href="#" data-toggle="modal" data-target="#showProductionModal{{ $production->id }}">{{ $production->order->order_number }}</a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showProductionModal{{ $production->id }}">{{ $production->order->customer_name }}</a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showProductionModal{{ $production->id }}">{{ $production->order->address }}</a>
                        </td>
                        <td>
                            @if($production->order->installer)
                                <span class="badge badge-info">{{ $production->order->installer->name }}</span>
                            @else
                                <span class="badge badge-danger">Не назначен</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-warning my-3">В производстве</span>
                        </td>
                        <td>
                            <a href="{{ route('employee.orders.show', $production->order) }}" class="btn btn-sm btn-outline-info">
                                <i class="fe fe-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#completeProductionModal{{ $production->id }}"
                                {{ !$production->order->installer_id ? 'disabled title="Сначала назначьте установщика в заказе"' : '' }}>
                                <i class="fe fe-check"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Модалка: Просмотр производства -->
                    <div class="modal fade" id="showProductionModal{{ $production->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Просмотр производства</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Заказ:</strong> №{{ $production->order->order_number }}</p>
                                    <p><strong>Клиент:</strong> {{ $production->order->customer_name }}</p>
                                    <p><strong>Адрес:</strong> {{ $production->order->address }}</p>
                                    <p><strong>Менеджер:</strong> {{ $production->order->manager->name ?? '—' }}</p>
                                    <p><strong>Установщик:</strong> 
                                        @if($production->order->installer)
                                            <span class="badge badge-info">{{ $production->order->installer->name }}</span>
                                        @else
                                            <span class="badge badge-danger">Не назначен</span>
                                        @endif
                                    </p>
                                    <p><strong>Заметки производства:</strong> {{ $production->notes ?? '—' }}</p>
                                    <p><strong>Статус:</strong> <span class="badge badge-warning">В производстве</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Модалка: Завершить производство -->
                    <div class="modal fade" id="completeProductionModal{{ $production->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Завершить производство для заказа №{{ $production->order->order_number }}</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('employee.productions.complete', $production) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="alert alert-info">
                                            <strong>Установщик:</strong> 
                                            @if($production->order->installer)
                                                {{ $production->order->installer->name }}
                                            @else
                                                <span class="text-danger">Не назначен! Сначала назначьте установщика в заказе.</span>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            <label for="notes{{ $production->id }}">Заметки (необязательно)</label>
                                            <textarea name="notes" id="notes{{ $production->id }}" class="form-control" rows="3" placeholder="Заметки по завершению производства"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-success" {{ !$production->order->installer_id ? 'disabled' : '' }}>
                                            Завершить производство
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
.table-productions th,
.table-productions td {
    padding: 0.1rem 0.2rem !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.table-productions td:last-child {
    white-space: normal !important;
    width: 120px !important;
}

.table-productions .btn {
    padding: 0.1rem 0.2rem !important;
    font-size: 0.7rem !important;
}
</style>
@endsection 