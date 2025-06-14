@extends('layouts.admin')

@vite(['resources/js/app.js'])

@section('title', 'Установка')

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
            Установка
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
            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#calendarModal">
                <i class="fe fe-calendar"></i> Календарь
                @if(request('manager_id'))
                    @if($selectedEmployee)
                        <small>({{ $selectedEmployee->name }})</small>
                    @endif
                @endif
            </button>
            <a href="{{ route('employee.installations.index') }}" class="btn btn-sm btn-outline-primary">
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
                        <a class="dropdown-item {{ request('manager_id') == $employee->id ? 'active' : '' }}" href="{{ route('employee.installations.index', ['manager_id' => $employee->id]) }}">
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
        <table class="table card-table table-vcenter text-nowrap table-installations">
            <thead>
                <tr>
                    <th>№</th>
                    <th>Клиент</th>
                    <th>Адрес</th>
                    <th>Дата установки</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($installations as $installation)
                    <tr>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $installation->id }}">
                                {{ $installation->id }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $installation->id }}">
                                {{ $installation->order->customer_name }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $installation->id }}">
                                {{ $installation->order->address }}
                            </a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showModal{{ $installation->id }}">
                                {{ $installation->installed_at ? $installation->installed_at->format('d.m.Y H:i') : '—' }}
                            </a>
                        </td>
                        <td>
                            @if($installation->installed_at)
                                <span class="badge badge-success my-3">Выполнен</span>
                            @else
                                <span class="badge badge-warning my-3">В процессе</span>
                            @endif
                            <form action="{{ route('employee.installations.destroy', $installation) }}" method="POST" class="d-inline mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Вы уверены, что хотите удалить эту установку?')">
                                    <i class="fe fe-trash"></i> Удалить
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Модалка: Просмотр --}}
                    <div class="modal fade" id="showModal{{ $installation->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Просмотр установки</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Заказ:</strong> Заказ №{{ $installation->order->order_number }}</p>
                                    <p><strong>Клиент:</strong> {{ $installation->order->customer_name }}</p>
                                    <p><strong>Адрес:</strong> {{ $installation->order->address }}</p>
                                    <p><strong>Установщик:</strong> {{ $installation->installer ? $installation->installer->name : 'Не назначен' }}</p>
                                    <p><strong>Дата установки:</strong> {{ $installation->installed_at ? $installation->installed_at->format('d.m.Y H:i') : '—' }}</p>
                                    <p><strong>Статус:</strong> 
                                        @if($installation->installed_at)
                                            <span class="badge badge-success">Завершена</span>
                                        @else
                                            <span class="badge badge-warning">В процессе</span>
                                        @endif
                                    </p>
                                    @if($installation->documentation)
                                        <p><strong>Документация:</strong> 
                                            <a href="{{ route('employee.documentations.show', $installation->documentation) }}">
                                                {{ Str::limit($installation->documentation->description, 50) }}
                                            </a>
                                        </p>
                                    @endif
                                    @if($installation->result_notes)
                                        <p><strong>Заметки по результату:</strong> {{ $installation->result_notes }}</p>
                                    @endif
                                    <p><strong>Вложения:</strong></p>
                                    <ul>
                                        @if($installation->attachments && $installation->attachments->isNotEmpty())
                                            @foreach ($installation->attachments as $attachment)
                                                <li><a href="{{ Storage::url($attachment->path) }}" target="_blank">{{ $attachment->filename }}</a></li>
                                            @endforeach
                                        @else
                                            <li>Нет вложений</li>
                                        @endif
                                    </ul>
                                    <div class="d-flex flex-column gap-2 mt-3">
                                        @if(!$installation->installed_at)
                                            <button type="button" class="btn btn-outline-success mb-2" data-toggle="modal" data-target="#confirmModal{{ $installation->id }}" data-dismiss="modal">
                                                <i class="fe fe-check"></i> Подтвердить установку
                                            </button>
                                        @endif
                                        <a href="{{ route('employee.installations.edit', $installation) }}" class="btn btn-outline-warning mb-2">
                                            <i class="fe fe-edit"></i> Редактировать
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Модалка: Подтверждение установки --}}
                    <div class="modal fade" id="confirmModal{{ $installation->id }}" tabindex="-1">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Подтверждение установки №{{ $installation->id }}</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <form method="POST" action="{{ route('employee.installations.confirm', $installation) }}">
                                    @csrf
                                    <div class="modal-body">
                                        <p>Подтвердить завершение установки для заказа №{{ $installation->order->order_number }}?</p>
                                        <div class="form-group mb-3">
                                            <label for="result_notes{{ $installation->id }}">Заметки о результате</label>
                                            <textarea name="result_notes" id="result_notes{{ $installation->id }}" class="form-control" rows="3" placeholder="Дополнительные заметки о выполненной установке">{{ $installation->result_notes }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                                        <button type="submit" class="btn btn-success">Подтвердить установку</button>
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

{{-- Календарь --}}
<div class="modal fade" id="calendarModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Календарь установок</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body p-0" id="calendarContent">
                <div id="calendar" data-type="installation"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let calendar;

        $('#calendarModal').on('shown.bs.modal', function () {
            $('#calendarContent').html('<div id="calendar" data-type="installation"></div>');
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
            const type = calendarEl.dataset.type;

            // Получаем параметр manager_id из URL
            const urlParams = new URLSearchParams(window.location.search);
            const managerId = urlParams.get('manager_id');

            // Обновляем заголовок календаря с информацией о фильтре
            const modalTitle = $('#calendarModal .modal-title');
            let calendarTitle = 'Календарь установок';
            
            // Получаем имя выбранного сотрудника из соответствующей ссылки в dropdown
            if (managerId) {
                const activeDropdownItem = document.querySelector(`a.dropdown-item[href*="manager_id=${managerId}"]`);
                if (activeDropdownItem) {
                    // Извлекаем только текст, исключая иконки
                    let employeeName = activeDropdownItem.textContent.trim();
                    // Убираем символ галочки если есть
                    employeeName = employeeName.replace(/\s*✓\s*$/, '').trim();
                    calendarTitle += ' - ' + employeeName;
                }
            }
            modalTitle.text(calendarTitle);

            if (calendar) {
                calendar.destroy();
            }

            // Формируем extraParams с учетом фильтрации
            const extraParams = { type: type };
            if (managerId) {
                extraParams.manager_id = managerId;
            }

            calendar = new window.FullCalendar.Calendar(calendarEl, {
                plugins: [window.FullCalendar.dayGridPlugin],
                locale: window.FullCalendar.ruLocale,
                initialView: 'dayGridMonth',
                height: '100%',
                events: {
                    url: '/employee/calendar/events',
                    method: 'GET',
                    extraParams: extraParams,
                    failure: function () {
                        alert('Ошибка загрузки событий!');
                    },
                },
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                }
            });
            calendar.render();
        });

        $('#calendarModal').on('hidden.bs.modal', function () {
            if (calendar) {
                calendar.destroy();
                calendar = null;
            }
        });
    });
</script>
@endsection

<style>
.table-installations th,
.table-installations td {
    padding: 0.1rem 0.2rem !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.table-installations td:last-child {
    white-space: normal !important;
    width: 120px !important;
}

.table-installations .btn {
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
</style> 