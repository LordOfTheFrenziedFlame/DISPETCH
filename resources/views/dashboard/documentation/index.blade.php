@extends('layouts.admin')

@vite(['resources/js/app.js'])

@section('title', 'Документация')

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
            Документация
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
                'calendarType' => 'documentation',
                'filterParam' => 'manager_id',
                'employees' => $managers ?? collect()
            ])
            <a href="{{ route('employee.documentations.index') }}" class="btn btn-sm btn-outline-primary">
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
                        <a class="dropdown-item {{ request('manager_id') == $employee->id ? 'active' : '' }}" href="{{ route('employee.documentations.index', ['manager_id' => $employee->id]) }}">
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
        <table class="table card-table table-vcenter text-nowrap table-documentation">
            <thead>
                <tr>
                    <th>№</th>
                    <th>Клиент</th>
                    <th>Договор</th>
                    <th>Вид товара</th>
                    <th>Конструктор</th>
                    <th>Дата завершения</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documentations as $documentation)
                    <tr>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $documentation->id }}">
                                {{ $documentation->id }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $documentation->id }}">
                                {{ optional($documentation->order)->customer_name }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $documentation->id }}">
                                {{ optional($documentation->contract)->contract_number ?? '—' }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $documentation->id }}">
                                {{ optional($documentation->contract)->product_type ?? '—' }}
                            </a>
                        </td>
                        <td>
                            {{ optional($documentation->constructor)->name ?: '—' }}
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $documentation->id }}">
                                {{ optional($documentation->contract?->documentation_due_at)->format('d.m.Y') ?? '—' }}
                            </a>
                        </td>
                        <td>
                            @if($documentation->completed_at)
                                <span class="btn btn-outline-success w-100 my-3">Выполнен</span>
                            @else
                                <span class="btn btn-outline-warning w-100 my-3">В процессе</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Модалка: Просмотр --}}
                    <div class="modal fade" id="showModal{{ $documentation->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Просмотр документации</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Номер договора:</strong> {{ optional($documentation->contract)->contract_number ?? '—' }}</p>
                                    <p><strong>Клиент:</strong> {{ optional($documentation->order)->customer_name }}</p>
                                    <p><strong>Адрес:</strong> {{ optional($documentation->order)->address }}</p>
                                    <p><strong>Дата завершения:</strong> {{ optional($documentation->contract?->documentation_due_at)->format('d.m.Y') ?? '—' }}</p>
                                    <p><strong>Описание:</strong> {{ $documentation->description }}</p>
                                    <p><strong>Вложения по заказу:</strong></p>
                                    @include('dashboard.partials.attachments-list', ['attachments' => $documentation->order->all_attachments])
                                    <div class="mt-4 d-flex gap-2">
                                        @if(!$documentation->completed_at)
                                        @if(auth('employees')->user()->role === 'manager')
                                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#confirmModalManager{{ $documentation->id }}">
                                                Подтвердить (Менеджер)
                                            </button>
                                        @endif
                                        @if(auth('employees')->user()->role === 'surveyor')
                                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#confirmModalSurveyor{{ $documentation->id }}">
                                                Подтвердить (Замерщик)
                                            </button>
                                        @endif
                                        @if(auth('employees')->user()->role === 'constructor')
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#confirmModalConstructor{{ $documentation->id }}">
                                                Подтвердить (Конструктор)
                                            </button>
                                        @endif
                                        @if(auth('employees')->user()->role === 'installer')
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmModalInstaller{{ $documentation->id }}">
                                                Подтвердить (Монтажник)
                                            </button>
                                            @endif
                                        @else
                                            <button type="button" class="btn btn-success" disabled>Выполнен</button>
                                        @endif
                                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#editModal{{ $documentation->id }}">Редактировать</button>
                                        <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#attachModal{{ $documentation->id }}" data-dismiss="modal">
                                            <i class="fe fe-paperclip"></i> Прикрепить медиа
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Модалка: Прикрепить медиа --}}
                    <div class="modal fade" id="attachModal{{ $documentation->id }}" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <form method="POST" action="{{ route('employee.documentations.addAttachment', $documentation) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Прикрепить медиа к документации</h5>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="media{{ $documentation->id }}">Выберите файлы</label>
                                            <input type="file" name="media[]" id="media{{ $documentation->id }}" class="form-control-file" required multiple>
                                            <small class="form-text text-muted">Разрешенные форматы: jpg, jpeg, png, pdf, doc, docx, xls, xlsx, ppt, pptx. Максимальный размер: 10MB</small>
                                        </div>
                                        <div class="form-group">
                                            <label for="comment{{ $documentation->id }}">Комментарий</label>
                                            <textarea name="comment" id="comment{{ $documentation->id }}" class="form-control" rows="3" placeholder="Комментарий к сдаче этапа (необязательно)"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-primary">Загрузить</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Модалка подтверждения для менеджера --}}
                    <div class="modal fade" id="confirmModalManager{{ $documentation->id }}" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <form method="POST" action="{{ route('employee.documentations.confirm', ['documentation' => $documentation->id, 'role' => 'manager']) }}">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Подтверждение документации</h5>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <strong>Заказ №{{ optional($documentation->order)->order_number ?? $documentation->order_id }}</strong> - {{ optional($documentation->order)->customer_name }}
                                            <br><small class="text-muted">{{ optional($documentation->order)->address }}</small>
                                            <br><small class="text-muted">Тел: {{ optional($documentation->order)->phone_number ?? '—' }}</small>
                                            @if(optional($documentation->order)->total_amount)
                                                <br><small class="text-muted">Стоимость: {{ number_format($documentation->order->total_amount, 0, '.', ' ') }} ₽</small>
                                            @endif
                                        </div>
                                        <p>Вы уверены, что хотите подтвердить документацию как менеджер?</p>
                                        <div class="form-group">
                                            <label for="notes{{ $documentation->id }}">Комментарий</label>
                                            <textarea name="notes" id="notes{{ $documentation->id }}" class="form-control" rows="3" placeholder="Комментарий к сдаче этапа (необязательно)"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-success">Да, подтвердить</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    {{-- Модалка подтверждения для замерщика --}}
                    <div class="modal fade" id="confirmModalSurveyor{{ $documentation->id }}" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <form method="POST" action="{{ route('employee.documentations.confirm', ['documentation' => $documentation->id, 'role' => 'surveyor']) }}">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Подтверждение</h5>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Вы уверены, что хотите подтвердить документацию как замерщик?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-info">Да, подтвердить</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Модалка подтверждения для конструктора --}}
                    <div class="modal fade" id="confirmModalConstructor{{ $documentation->id }}" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <form method="POST" action="{{ route('employee.documentations.confirm', ['documentation' => $documentation->id, 'role' => 'constructor']) }}">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Подтверждение</h5>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Вы уверены, что хотите подтвердить документацию как конструктор?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-warning">Да, подтвердить</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Модалка подтверждения для монтажника --}}
                    <div class="modal fade" id="confirmModalInstaller{{ $documentation->id }}" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <form method="POST" action="{{ route('employee.documentations.confirm', ['documentation' => $documentation->id, 'role' => 'installer']) }}">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Подтверждение</h5>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Вы уверены, что хотите подтвердить документацию как монтажник?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-primary">Да, подтвердить</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Модалка: Редактирование --}}
                    <div class="modal fade" id="editModal{{ $documentation->id }}" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <form method="POST" action="{{ route('employee.documentations.update', $documentation) }}">
                                @csrf
                                @method('PATCH')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Редактировать документацию</h5>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="description{{ $documentation->id }}">Описание</label>
                                            <textarea name="description" id="description{{ $documentation->id }}" class="form-control" rows="3">{{ $documentation->description }}</textarea>
                                        </div>
                                        <div class="form-group mt-3">
                                            <label for="completed_at{{ $documentation->id }}">Дата завершения</label>
                                            <input type="datetime-local" name="completed_at" id="completed_at{{ $documentation->id }}" class="form-control" value="{{ $documentation->completed_at ? \Carbon\Carbon::parse($documentation->completed_at)->format('Y-m-d\TH:i') : '' }}">
                                        </div>
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
.table-documentation th,
.table-documentation td {
    padding: 0.1rem 0.2rem !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.table-documentation td:last-child {
    white-space: normal !important;
    width: 120px !important;
}

.table-documentation .btn {
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
    min-height: 0;
    min-width: 0;
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