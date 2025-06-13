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
            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#calendarModal">
                <i class="fe fe-calendar"></i> Календарь
                @if(request('manager_id'))
                    @if($selectedEmployee)
                        <small>({{ $selectedEmployee->name }})</small>
                    @endif
                @endif
            </button>
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
                    <th>Клиент</th>
                    <th>Адрес</th>
                    <th>Дата подписания</th>
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
                                {{ $contract->signed_at ? \Carbon\Carbon::parse($contract->signed_at)->format('d.m.Y H:i') : '—' }}
                            </a>
                        </td>
                        <td class="actions-cell" style="min-width: 110px; white-space: nowrap;">
                            @if($contract->signed_at)
                                <span class="badge badge-success align-middle" style="display:inline-block;vertical-align:middle;">Выполнен</span>
                            @else
                                <span class="badge badge-warning align-middle" style="display:inline-block;vertical-align:middle;">В процессе</span>
                            @endif
                            <form action="{{ route('employee.contracts.destroy', $contract) }}" method="POST" class="d-inline p-0 m-0" style="display:inline-block;vertical-align:middle;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm p-1 m-0" style="min-width:70px;" onclick="return confirm('Вы уверены, что хотите удалить этот договор?')">
                                    <i class="fe fe-trash"></i> Удалить
                                </button>
                            </form>
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
                                    <p><strong>Клиент:</strong> {{ optional($contract->order)->customer_name }}</p>
                                    <p><strong>Адрес:</strong> {{ optional($contract->order)->address }}</p>
                                    <p><strong>Дата подписания:</strong> {{ $contract->signed_at ? \Carbon\Carbon::parse($contract->signed_at)->format('d.m.Y H:i') : '—' }}</p>
                                    <p><strong>Медиа:</strong></p>
                                    <ul>
                                        @foreach ($contract->attachments as $attachment)
                                            <li><a href="{{ Storage::url($attachment->path) }}" target="_blank">{{ $attachment->filename }}</a></li>
                                        @endforeach
                                    </ul>
                                    <div class="d-flex flex-column gap-2 mt-3">
                                        @if(!$contract->signed_at)
                                        <button type="button" class="btn btn-outline-success mb-2" data-toggle="modal" data-target="#signModal{{ $contract->id }}" data-dismiss="modal">
                                            <i class="fe fe-check"></i> Сдать договор
                                        </button>
                                        @else
                                            <span class="badge badge-success">Выполнен</span>
                                        @endif
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
                                            <label for="comment{{ $contract->id }}">Комментарий</label>
                                            <textarea name="comment" id="comment{{ $contract->id }}" class="form-control" rows="3" placeholder="Комментарий к сдаче договора (необязательно)"></textarea>
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
                <h5 class="modal-title">Календарь договоров</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body p-0" id="calendarContent">
                <div id="calendar" data-type="contract"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let calendar;

        $('#calendarModal').on('shown.bs.modal', function () {
            $('#calendarContent').html('<div id="calendar" data-type="contract"></div>');
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
            const type = calendarEl.dataset.type;

            // Получаем параметр manager_id из URL
            const urlParams = new URLSearchParams(window.location.search);
            const managerId = urlParams.get('manager_id');

            // Обновляем заголовок календаря с информацией о фильтре
            const modalTitle = $('#calendarModal .modal-title');
            let calendarTitle = 'Календарь договоров';
            
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
