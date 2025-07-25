@extends('layouts.admin')

@vite(['resources/js/app.js'])

@section('title', 'Договоры')

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

<div class="card my-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            Договоры
            @if(request('manager_id') && $selectedEmployee)
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
        </h3>
        <div class="btn-group">
            @include('dashboard.partials.universal-calendar', [
                'calendarType' => 'contract',
                'filterParam' => 'manager_id',
                'employees' => $managers ?? collect()
            ])
            <a href="{{ route('employee.contracts.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fe fe-rotate-ccw"></i> 
                @if(request('manager_id'))
                    Сбросить фильтр
                @else
                    Обновить
                @endif
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" id="sortEmployeeDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fe fe-user"></i> Фильтр по сотрудникам
                </button>
                <div class="dropdown-menu" aria-labelledby="sortEmployeeDropdown">
                    @foreach($employees as $employee)
                        <a class="dropdown-item {{ request('manager_id') == $employee->id ? 'active' : '' }}" href="{{ route('employee.contracts.index', ['manager_id' => $employee->id]) }}">
                            {{ $employee->name }} 
                            <small class="text-muted">
                                @if($employee->role === 'manager')
                                    (Менеджер)
                                @elseif($employee->role === 'surveyor')
                                    (Замерщик)
                                @elseif($employee->role === 'constructor')
                                    (Конструктор)
                                @elseif($employee->role === 'installer')
                                    (Монтажник)
                                @endif
                            </small>
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
        <table class="table card-table table-vcenter text-nowrap table-contracts">
            <thead>
                <tr>
                    <th>№</th>
                    <th>№ ДОГОВОРА</th>
                    <th>Клиент</th>
                    <th>Адрес</th>
                    <th>ВИД ТОВАРА</th>
                    <th>Дата создания</th>
                    <th>Документация</th>
                    <th>Производство</th>
                    <th>Установка</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contracts as $contract)
                    <tr>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $contract->id }}">
                                {{ $contract->id }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $contract->id }}">
                                {{ $contract->contract_number ?: '—' }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $contract->id }}">
                                {{ optional($contract->order)->customer_name }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $contract->id }}">
                                {{ optional($contract->order)->address }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $contract->id }}">
                                {{ $contract->product_type ?: '—' }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $contract->id }}">
                                {{ $contract->created_at ? \Carbon\Carbon::parse($contract->created_at)->format('d.m.Y') : '—' }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $contract->id }}">
                                {{ $contract->documentation_due_at ? \Carbon\Carbon::parse($contract->documentation_due_at)->format('d.m.Y') : '—' }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $contract->id }}">
                                {{ $contract->ready_date ? \Carbon\Carbon::parse($contract->ready_date)->format('d.m.Y') : '—' }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $contract->id }}">
                                {{ $contract->installation_date ? \Carbon\Carbon::parse($contract->installation_date)->format('d.m.Y') : '—' }}
                            </a>
                        </td>
                        <td>
                            @if($contract->signed_at)
                                <span class="btn btn-outline-success w-100 my-3">Выполнен</span>
                            @else
                                <span class="btn btn-outline-warning w-100 my-3">В процессе</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Модалка: Просмотр --}}
                    <div class="modal fade" id="showModal{{ $contract->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Просмотр договора</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Номер договора</h6>
                                            <p>{{ $contract->contract_number ?: '—' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Клиент</h6>
                                            <p>{{ optional($contract->order)->customer_name }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Комментарий</h6>
                                            <p>{{ $contract->comment ?: '—' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Адрес</h6>
                                            <p>{{ optional($contract->order)->address }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Конструктор</h6>
                                            <p>{{ optional($contract->constructor)->name ?: '—' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Дата создания</h6>
                                            <p>{{ $contract->created_at ? $contract->created_at->format('d.m.Y') : '—' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Сумма договора</h6>
                                            <p>{{ $contract->final_amount ? number_format($contract->final_amount, 2, '.', ' ') . ' руб.' : '—' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Дата готовности документации</h6>
                                            <p>{{ $contract->documentation_due_at ? $contract->documentation_due_at->format('d.m.Y') : '—' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Дата установки</h6>
                                            <p>{{ $contract->installation_date ? $contract->installation_date->format('d.m.Y') : '—' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Вид товара</h6>
                                            <p>{{ $contract->product_type ?: '—' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Дата готовности</h6>
                                            <p>{{ $contract->ready_date ? $contract->ready_date->format('d.m.Y') : '—' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Монтажник</h6>
                                            <p>{{ optional($contract->order->installer)->name ?: '—' }}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <h6>Вложения</h6>
                                    @include('dashboard.partials.attachments-list', ['attachments' => $contract->order->all_attachments])
                                    <div class="d-flex flex-column gap-2 mt-3">
                                        <button type="button" class="btn btn-outline-warning mb-2" data-toggle="modal" data-target="#editModal{{ $contract->id }}" data-dismiss="modal">
                                            <i class="fe fe-edit"></i> Редактировать
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Модалка: Сдать договор --}}
                    <div class="modal fade" id="signModal{{ $contract->id }}" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Сдать договор №{{ $contract->id }}</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <form method="POST" action="{{ route('employee.contracts.sign', $contract) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label for="signed_file{{ $contract->id }}">Прикрепить файл</label>
                                            <input type="file" name="signed_file" id="signed_file{{ $contract->id }}" class="form-control-file" required>
                                        </div>
                                       <div class="form-group mb-3">
                                            <label for="final_amount{{ $contract->id }}">Итоговая сумма, ₽</label>
                                            <input type="number" step="0.01" name="final_amount" id="final_amount{{ $contract->id }}" class="form-control" value="{{ $contract->final_amount }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="contract_number{{ $contract->id }}">Номер договора</label>
                                            <input type="text" name="contract_number" id="contract_number{{ $contract->id }}" class="form-control" value="{{ $contract->contract_number }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="documentation_due_at{{ $contract->id }}">Срок подготовки документации</label>
                                            <input type="date" name="documentation_due_at" id="documentation_due_at{{ $contract->id }}" class="form-control" value="{{ optional($contract->documentation_due_at)->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="constructor_id_sign{{ $contract->id }}">Конструктор</label>
                                            <select name="constructor_id" id="constructor_id_sign{{ $contract->id }}" class="form-control">
                                                <option value="">—</option>
                                                @foreach($constructorsList as $c)
                                                    <option value="{{ $c->id }}" {{ $c->id == $contract->constructor_id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="installation_date{{ $contract->id }}">Дата установки</label>
                                            <input type="date" name="installation_date" id="installation_date{{ $contract->id }}" class="form-control" value="{{ optional($contract->installation_date)->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="installer_id_sign{{ $contract->id }}">Установщик</label>
                                            <select name="installer_id" id="installer_id_sign{{ $contract->id }}" class="form-control">
                                                <option value="">—</option>
                                                @foreach($installersList as $inst)
                                                    <option value="{{ $inst->id }}" {{ $inst->id == $contract->order->installer_id ? 'selected' : '' }}>{{ $inst->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="product_type{{ $contract->id }}">Вид товара</label>
                                            <input type="text" name="product_type" id="product_type{{ $contract->id }}" class="form-control" value="{{ $contract->product_type }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="ready_date{{ $contract->id }}">Дата готовности</label>
                                            <input type="date" name="ready_date" id="ready_date{{ $contract->id }}" class="form-control" value="{{ optional($contract->ready_date)->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="comment{{ $contract->id }}">Комментарий</label>
                                            <textarea name="comment" id="comment{{ $contract->id }}" class="form-control" rows="3" placeholder="Комментарий к сдаче договора (необязательно)">{{ $contract->comment }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-success">Сдать договор</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Модалка: Редактирование договора  --}}
                    <div class="modal fade" id="editModal{{ $contract->id }}" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <form method="POST" action="{{ route('employee.contracts.update', $contract) }}">
                                @csrf
                                @method('PATCH')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Редактировать договор</h5>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label for="final_amount_edit{{ $contract->id }}">Итоговая сумма, ₽</label>
                                            <input type="number" step="0.01" name="final_amount" id="final_amount_edit{{ $contract->id }}" class="form-control" value="{{ $contract->final_amount }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="contract_number_edit{{ $contract->id }}">Номер договора</label>
                                            <input type="text" name="contract_number" id="contract_number_edit{{ $contract->id }}" class="form-control" value="{{ $contract->contract_number }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="documentation_due_at_edit{{ $contract->id }}">Срок подготовки документации</label>
                                            <input type="date" name="documentation_due_at" id="documentation_due_at_edit{{ $contract->id }}" class="form-control" value="{{ optional($contract->documentation_due_at)->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="constructor_id_edit{{ $contract->id }}">Конструктор</label>
                                            <select name="constructor_id" id="constructor_id_edit{{ $contract->id }}" class="form-control">
                                                <option value="">—</option>
                                                @foreach($constructorsList as $c)
                                                    <option value="{{ $c->id }}" {{ $c->id == $contract->constructor_id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="installation_date{{ $contract->id }}">Дата установки</label>
                                            <input type="date" name="installation_date" id="installation_date{{ $contract->id }}" class="form-control" value="{{ optional($contract->installation_date)->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="installer_id_edit{{ $contract->id }}">Установщик</label>
                                            <select name="installer_id" id="installer_id_edit{{ $contract->id }}" class="form-control">
                                                <option value="">—</option>
                                                @foreach($installersList as $inst)
                                                    <option value="{{ $inst->id }}" {{ $inst->id == $contract->order->installer_id ? 'selected' : '' }}>{{ $inst->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="product_type{{ $contract->id }}">Вид товара</label>
                                            <input type="text" name="product_type" id="product_type{{ $contract->id }}" class="form-control" value="{{ $contract->product_type }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="ready_date_edit{{ $contract->id }}">Дата готовности</label>
                                            <input type="date" name="ready_date" id="ready_date_edit{{ $contract->id }}" class="form-control" value="{{ optional($contract->ready_date)->format('Y-m-d') }}">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="comment_edit{{ $contract->id }}">Комментарий</label>
                                            <textarea name="comment" id="comment_edit{{ $contract->id }}" class="form-control" rows="3">{{ $contract->comment }}</textarea>
                                        </div>
                                        <input type="hidden" name="order_id" value="{{ $contract->order_id }}">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-primary">Сохранить</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


@endsection

<style>
.table-contracts th,
.table-contracts td {
    padding: 0.1rem 0.2rem !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.table-contracts td:last-child {
    white-space: normal !important;
    width: 120px !important;
}

.table-contracts .btn {
    padding: 0.1rem 0.2rem !important;
    font-size: 0.7rem !important;
}

.modal-fullscreen,
.modal-fullscreen .modal-dialog,
.modal-fullscreen .modal-content,
.modal-fullscreen .modal-body {
    width: 100vw;
    max-width: 100vw;
    height: 100vh;
    max-height: 100vh;
    margin: 0;
    padding: 0 !important;
    border-radius: 0;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: stretch;
}

#calendar {
    flex: 1 1 auto;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
}

.modal-fullscreen,
.modal-fullscreen .modal-dialog {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    margin: 0 !important;
    z-index: 1055;
}

.actions-cell > * {
    margin-bottom: 0 !important;
    margin-right: 2px !important;
}
.actions-cell .btn,
.actions-cell .badge {
    margin-bottom: 0 !important;
    margin-right: 2px !important;
}
</style>
