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
            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#calendarModal">
                <i class="fe fe-calendar"></i> Календарь
                @if(request('manager_id'))
                    @if($selectedEmployee)
                        <small>({{ $selectedEmployee->name }})</small>
                    @endif
                @endif
            </button>
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
                    <i class="fe fe-user"></i> Фильтр по сотрудникам
                </button>
                <div class="dropdown-menu" aria-labelledby="sortEmployeeDropdown">
                    @foreach($employees as $employee)
                        <a class="dropdown-item {{ request('manager_id') == $employee->id ? 'active' : '' }}" href="{{ route('employee.productions.index', ['manager_id' => $employee->id]) }}">
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
        <table class="table card-table table-vcenter text-nowrap table-productions">
            <thead>
                <tr>
                    <th>№</th>
                    <th>№ Заказа</th>
                    <th>№ Договора</th>
                    <th>Клиент</th>
                    <th>Адрес</th>
                    <th>Дата готовности</th>
                    <th>Дата установки</th>
                    <th>Заметки</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productions as $production)
                    <tr>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showProductionModal{{ $production->id }}">{{ $production->id }}</a>
                        </td>
                        <td>
                             <a href="#" data-toggle="modal" data-target="#showProductionModal{{ $production->id }}">{{ optional($production->order)->order_number ?: '—' }}</a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showProductionModal{{ $production->id }}">{{ optional(optional($production->order)->contract)->contract_number ?? '—' }}</a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showProductionModal{{ $production->id }}">{{ optional($production->order)->customer_name ?: '—' }}</a>
                        </td>
                        <td>
                            <a href="#" data-toggle="modal" data-target="#showProductionModal{{ $production->id }}">{{ optional($production->order)->address ?: '—' }}</a>
                        </td>
                        <td>
                            {{ optional(optional($production->order)->contract)->ready_date ? \Carbon\Carbon::parse(optional(optional($production->order)->contract)->ready_date)->format('d.m.Y') : '—' }}
                        </td>
                        <td>
                            {{ optional(optional($production->order)->contract)->installation_date ? \Carbon\Carbon::parse(optional(optional($production->order)->contract)->installation_date)->format('d.m.Y') : '—' }}
                        </td>
                        <td>
                            {{ \Illuminate\Support\Str::limit($production->notes ?? '—', 50) }}
                        </td>
                        <td>
                            @if($production->completed_at)
                                <span class="btn btn-outline-success w-100 my-3">Выполнен</span>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-success w-100 my-3" data-toggle="modal" data-target="#completeProductionModal{{ $production->id }}"
                                    {{ !optional($production->order)->installer_id ? 'disabled title="Сначала назначьте установщика в заказе"' : '' }}>
                                    <i class="fe fe-check"></i> Завершить
                                </button>
                            @endif
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
                                    <p><strong>Договор №:</strong> {{ optional(optional($production->order)->contract)->contract_number ?? '—' }}</p>
                                    <p><strong>Заказ:</strong> №{{ optional($production->order)->order_number ?: '—' }}</p>
                                    <p><strong>Клиент:</strong> {{ optional($production->order)->customer_name ?: '—' }}</p>
                                    <p><strong>Адрес:</strong> {{ optional($production->order)->address ?: '—' }}</p>
                                    <p><strong>Менеджер:</strong> {{ optional($production->order)->manager->name ?? '—' }}</p>
                                    <p><strong>Установщик:</strong> 
                                        @if(optional($production->order)->installer)
                                            <span class="badge badge-info">{{ optional($production->order)->installer->name }}</span>
                                        @else
                                            <span class="badge badge-danger">Не назначен</span>
                                        @endif
                                    </p>
                                    <p><strong>Дата готовности (по договору):</strong> {{ optional(optional($production->order)->contract)->ready_date ? \Carbon\Carbon::parse(optional(optional($production->order)->contract)->ready_date)->format('d.m.Y') : '—' }}</p>
                                    <p><strong>Дата установки (по договору):</strong> {{ optional(optional($production->order)->contract)->installation_date ? \Carbon\Carbon::parse(optional(optional($production->order)->contract)->installation_date)->format('d.m.Y') : '—' }}</p>
                                    <p><strong>Заметки производства:</strong> {{ $production->notes ?? '—' }}</p>
                                    <p><strong>Вложения по заказу:</strong></p>
                                    @include('dashboard.partials.attachments-list', ['attachments' => $production->order->all_attachments])
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Модалка: Завершить производство -->
                    <div class="modal fade" id="completeProductionModal{{ $production->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Завершить производство для заказа №{{ optional($production->order)->order_number ?: '—' }}</h5>
                                    <button type="button" class="close" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('employee.productions.complete', $production) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <strong>Заказ №{{ optional($production->order)->order_number ?? $production->order_id }}</strong> - {{ optional($production->order)->customer_name }}
                                            <br><small class="text-muted">{{ optional($production->order)->address }}</small>
                                            <br><small class="text-muted">Тел: {{ optional($production->order)->phone_number ?? '—' }}</small>
                                            @if(optional($production->order)->total_amount)
                                                <br><small class="text-muted">Стоимость: {{ number_format($production->order->total_amount, 0, '.', ' ') }} ₽</small>
                                            @endif
                                        </div>
                                        <div class="alert alert-info">
                                            <strong>Установщик:</strong> 
                                            @if(optional($production->order)->installer)
                                                {{ optional($production->order)->installer->name }}
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
                                        <button type="submit" class="btn btn-success" {{ !optional($production->order)->installer_id ? 'disabled' : '' }}>
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

/* Дополнительные стили для полноэкранного модального окна календаря */
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

/* Фикс для отображения выпадающих меню поверх таблицы */
.table-responsive {
    overflow: visible !important;
}
.dropdown-menu {
    z-index: 1055 !important;
}
</style>

<!-- Календарь -->
<div class="modal fade" id="calendarModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Календарь производства</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body p-0" id="calendarContent">
                <div id="calendar" data-type="production"></div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let calendar;

        $('#calendarModal').on('shown.bs.modal', function () {
            $('#calendarContent').html('<div id="calendar" data-type="production"></div>');
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
            const type = calendarEl.dataset.type;

            // Получаем параметр manager_id из URL
            const urlParams = new URLSearchParams(window.location.search);
            const managerId = urlParams.get('manager_id');

            // Обновляем заголовок календаря с информацией о фильтре
            const modalTitle = $('#calendarModal .modal-title');
            let calendarTitle = 'Календарь производства';
            
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
.table-production th,
.table-production td {
    padding: 0.1rem 0.2rem !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.table-production td:last-child {
    white-space: normal !important;
    width: 120px !important;
}

.table-production .btn {
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