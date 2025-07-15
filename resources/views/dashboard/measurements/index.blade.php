@extends('layouts.admin')

@vite(['resources/js/app.js'])

@section('title', 'Замеры')

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
                Замеры
                            @if(request('currentUserMeasurements') && $selectedEmployee)
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
                    'calendarType' => 'measurement',
                    'filterParam' => 'currentUserMeasurements',
                    'employees' => $employees ?? collect()
                ])
                <a href="{{ route('employee.measurements.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fe fe-rotate-ccw"></i> 
                    @if(request('currentUserMeasurements'))
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
                    @foreach ($employees as $employee)
                            <a class="dropdown-item {{ request('currentUserMeasurements') == $employee->id ? 'active' : '' }}" href="{{ route('employee.measurements.index', ['currentUserMeasurements' => $employee->id]) }}">
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
                                @if(request('currentUserMeasurements') == $employee->id)
                                    <i class="fe fe-check ms-2"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap table-measurements">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>№ Заказа</th>
                        <th>Клиент</th>
                        <th>Телефон</th>
                        <th>Адрес</th>
                        <th>Замерщик</th>
                        <th>Дата встречи</th>
                        <th>Дата замера</th>
                        <th>Комментарий</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($measurements as $measurement)
                        <tr>
                            <td>
                                <a href="#" data-toggle="modal" data-target="#showModal{{ $measurement->id }}">
                                    {{ $measurement->id }}
                                </a>
                            </td>
                            <td>
                                <a href="#" data-toggle="modal" data-target="#showModal{{ $measurement->id }}">
                                    {{ optional($measurement->order)->order_number ?? '—' }}
                                </a>
                            </td>
                            <td>
                                <a href="#" data-toggle="modal" data-target="#showModal{{ $measurement->id }}">
                                    {{ optional($measurement->order)->customer_name }}
                                </a>
                            </td>
                            <td>
                                <a href="#" data-toggle="modal" data-target="#showModal{{ $measurement->id }}">
                                    {{ optional($measurement->order)->phone_number ?? '—' }}
                                </a>
                            </td>
                            <td>
                                <a href="#" data-toggle="modal" data-target="#showModal{{ $measurement->id }}">
                                    {{ optional($measurement->order)->address }}
                                </a>
                            </td>
                            <td>
                                <a href="#" data-toggle="modal" data-target="#showModal{{ $measurement->id }}">
                                    {{ optional($measurement->surveyor)->name }}
                                </a>
                            </td>
                            <td>
                                <a href="#" data-toggle="modal" data-target="#showModal{{ $measurement->id }}">
                                    {{ $measurement->initial_meeting_at ? \Carbon\Carbon::parse($measurement->initial_meeting_at)->format('d.m.Y H:i') : '—' }}
                                </a>
                            </td>
                            <td>
                                <a href="#" data-toggle="modal" data-target="#showModal{{ $measurement->id }}">
                                    {{ $measurement->measured_at ? \Carbon\Carbon::parse($measurement->measured_at)->format('d.m.Y H:i') : '—' }}
                                </a>
                            </td>
                            <td>
                                {{ \Illuminate\Support\Str::limit($measurement->notes ?? '—', 50) }}
                            </td>
                            <td style="white-space: normal;">
                                @if($measurement->isPending())
                                    <button type="button" class="btn btn-outline-success my-3" data-toggle="modal" data-target="#completeModal{{ $measurement->id }}">
                                        <i class="fe fe-check"></i> Отметить как сданный
                                    </button>
                                @elseif($measurement->isCompleted())
                                    <span class="btn btn-outline-success w-100 my-3">Выполнен</span>
                                @elseif($measurement->isCancelled())
                                    <span class="btn btn-outline-danger w-100 my-3">Отменён</span>
                                @endif
                            </td>
                        </tr>

                       
                        <div class="modal fade" id="showModal{{ $measurement->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Просмотр замера</h5>
                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Номер заказа:</strong> {{ optional($measurement->order)->order_number ?? '—' }}</p>
                                        <p><strong>Клиент:</strong> {{ optional($measurement->order)->customer_name }}</p>
                                                <p><strong>Телефон:</strong> {{ optional($measurement->order)->phone_number ?? '—' }}</p>
                                                <p><strong>Email:</strong> {{ optional($measurement->order)->email ?? '—' }}</p>
                                        <p><strong>Адрес:</strong> {{ optional($measurement->order)->address }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Менеджер:</strong> {{ optional($measurement->order->manager)->name ?? '—' }}</p>
                                                <p><strong>Замерщик:</strong> {{ optional($measurement->surveyor)->name ?? '—' }}</p>
                                                <p><strong>Дата встречи (первичная):</strong> {{ $measurement->initial_meeting_at ? \Carbon\Carbon::parse($measurement->initial_meeting_at)->format('d.m.Y H:i') : '—' }}</p>
                                        <p><strong>Дата замера:</strong> {{ $measurement->measured_at ? \Carbon\Carbon::parse($measurement->measured_at)->format('d.m.Y H:i') : '—' }}</p>
                                                <p><strong>Стоимость:</strong> {{ optional($measurement->order)->total_amount ? number_format($measurement->order->total_amount, 0, '.', ' ') . ' ₽' : '—' }}</p>
                                            </div>
                                        </div>
                                        @if(optional($measurement->order)->product_name)
                                            <p><strong>Продукция:</strong> {{ $measurement->order->product_name }}</p>
                                        @endif
                                        @if(optional($measurement->order)->notes)
                                            <p><strong>Примечания к заказу:</strong> {{ $measurement->order->notes }}</p>
                                        @endif
                                        @if($measurement->notes)
                                            <p><strong>Примечания к замеру:</strong> {{ $measurement->notes }}</p>
                                        @endif
                                        <p><strong>Вложения по заказу:</strong></p>
                                        @include('dashboard.partials.attachments-list', ['attachments' => $measurement->order->all_attachments])
                                        <div class="d-flex flex-column gap-2 mt-3">
                                            <button type="button" class="btn btn-outline-primary mb-2" data-toggle="modal" data-target="#setTimeModal{{ $measurement->id }}" data-dismiss="modal">
                                                <i class="fe fe-clock"></i> Назначить время
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary mb-2" data-toggle="modal" data-target="#attachModal{{ $measurement->id }}" data-dismiss="modal">
                                                <i class="fe fe-paperclip"></i> Прикрепить медиа
                                            </button>
                                            @if(Auth::guard('employees')->user()->role === 'manager')
                                                <form method="POST" action="{{ route('employee.measurements.destroy', $measurement->id) }}" onsubmit="return confirm('Удалить замер?')" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger w-100" type="submit">
                                                        <i class="fe fe-trash"></i> Удалить
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Модалка: Назначить время --}}
                        <div class="modal fade" id="setTimeModal{{ $measurement->id }}" tabindex="-1">
                            <div class="modal-dialog" role="document">
                                <form method="POST" action="{{ route('employee.measurements.timeChange', $measurement) }}">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Назначить время замера</h5>
                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <strong>Заказ №{{ optional($measurement->order)->order_number ?? $measurement->order_id }}</strong> - {{ optional($measurement->order)->customer_name }}
                                                <br><small class="text-muted">{{ optional($measurement->order)->address }}</small>
                                                <br><small class="text-muted">Тел: {{ optional($measurement->order)->phone_number ?? '—' }}</small>
                                            </div>
                                            <div class="form-group">
                                                <label for="time_change{{ $measurement->id }}">Дата и время замера</label>
                                                <input type="datetime-local" name="time_change" id="time_change{{ $measurement->id }}" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="notes{{ $measurement->id }}">Примечание</label>
                                                <textarea name="notes" id="notes{{ $measurement->id }}" class="form-control" placeholder="Примечание (необязательно)">{{ $measurement->notes }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Сохранить</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Модалка: Прикрепить медиа --}}
                        <div class="modal fade" id="attachModal{{ $measurement->id }}" tabindex="-1">
                            <div class="modal-dialog" role="document">
                                <form method="POST" action="{{ route('employee.measurements.addAttachment', $measurement) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Прикрепить медиа</h5>
                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <strong>Заказ №{{ optional($measurement->order)->order_number ?? $measurement->order_id }}</strong> - {{ optional($measurement->order)->customer_name }}
                                                <br><small class="text-muted">{{ optional($measurement->order)->address }}</small>
                                            </div>
                                            <div class="form-group">
                                                <label for="media{{ $measurement->id }}">Медиа файлы</label>
                                                <input type="file" name="media[]" id="media{{ $measurement->id }}" class="form-control-file" required multiple>
                                                <small class="form-text text-muted">Разрешенные форматы: jpg, jpeg, png, pdf, doc, docx, xls, xlsx, ppt, pptx. Максимальный размер: 10MB</small>
                                            </div>
                                            <div class="form-group">
                                                <label for="comment{{ $measurement->id }}">Комментарий</label>
                                                <textarea name="comment" id="comment{{ $measurement->id }}" class="form-control" placeholder="Комментарий к файлам (необязательно)"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Загрузить</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Модалка: Отметить как сданный --}}
                        <div class="modal fade" id="completeModal{{ $measurement->id }}" tabindex="-1">
                            <div class="modal-dialog" role="document">
                                <form method="POST" action="{{ route('employee.measurements.complete', $measurement) }}">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Отметить как сданный</h5>
                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <strong>Заказ №{{ optional($measurement->order)->order_number ?? $measurement->order_id }}</strong> - {{ optional($measurement->order)->customer_name }}
                                                <br><small class="text-muted">{{ optional($measurement->order)->address }}</small>
                                                <br><small class="text-muted">Тел: {{ optional($measurement->order)->phone_number ?? '—' }}</small>
                                                @if(optional($measurement->order)->total_amount)
                                                    <br><small class="text-muted">Стоимость: {{ number_format($measurement->order->total_amount, 0, '.', ' ') }} ₽</small>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="notes{{ $measurement->id }}">Комментарий</label>
                                                <textarea name="notes" id="notes{{ $measurement->id }}" class="form-control" placeholder="Комментарий к сдаче замера (необязательно)"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Отметить как сданный</button>
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
.table-measurements th,
.table-measurements td {
    padding: 0.1rem 0.2rem !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.table-measurements td:last-child {
    white-space: normal !important;
    width: 120px !important;
}

.table-measurements .btn {
    padding: 0.1rem 0.2rem !important;
    font-size: 0.7rem !important;
}

@media (min-width: 992px) {
    .table-responsive {
        overflow-x: hidden;
    }
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
</style>
