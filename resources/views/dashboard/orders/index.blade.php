<!-- Start of Selection -->
@extends('layouts.admin')

@vite(['resources/js/app.js'])

@section('title', 'Заказы')

@section('content')
    @php
        $editOrder = $editOrder ?? null;
    @endphp
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
                Заказы
                @if(request('manager_id'))
                    @php
                        $selectedManager = $managers->find(request('manager_id'));
                    @endphp
                    @if($selectedManager)
                        <small class="text-muted">- {{ $selectedManager->name }}</small>
                    @endif
                @endif
            </h3>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                <form method="GET" action="{{ route('employee.orders.orderByNumber') }}" class="d-flex align-items-center me-2 mb-0">
                    <input type="text" name="order_number" class="form-control form-control-sm me-2" placeholder="Поиск по номеру заказа" value="{{ request('order_number') }}">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Поиск</button>
                </form>
                <a href="{{ route('employee.orders.index') }}" class="btn btn-sm btn-outline-primary me-2">
                    <i class="fe fe-rotate-ccw"></i> 
                    @if(request('manager_id'))
                        Сбросить фильтр
                    @else
                        Обновить
                    @endif
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary me-2" data-toggle="modal" data-target="#calendarModal">
                    <i class="fe fe-calendar"></i> Календарь
                    @if(request('manager_id') && isset($selectedManager) && $selectedManager)
                        <small>({{ $selectedManager->name }})</small>
                    @endif
                </button>
                <div class="dropdown me-2">
                    <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" id="sortManagerDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fe fe-user"></i> Фильтр по менеджерам
                    </button>
                    <div class="dropdown-menu" aria-labelledby="sortManagerDropdown">
                        @foreach($managers as $manager)
                            <a class="dropdown-item {{ request('manager_id') == $manager->id ? 'active' : '' }}" href="{{ route('employee.orders.index', ['manager_id' => $manager->id]) }}">
                                {{ $manager->name }}
                                @if(request('manager_id') == $manager->id)
                                    <i class="fe fe-check ms-2"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#createOrderModal">
                    <i class="fe fe-plus"></i> Создать заказ
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap table-orders">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Клиент</th>
                        <th>Продукт</th>
                        <th>Стоимость</th>
                        <th>Менеджер</th>
                        <th>Встреча</th>
                        <th>Замер</th>
                        <th>Договор</th>
                        <th>Документация</th>
                        <th>Установка</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ $order->id }}
                                </a>
                            </td>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ $order->customer_name }}
                                </a>
                            </td>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ $order->product_name }}
                                </a>
                            </td>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ $order->total_amount ? number_format($order->total_amount, 0, '.', ' ') . ' ₽' : '—' }}
                                </a>
                            </td>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ optional($order->manager)->name }}
                                </a>
                            </td>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ $order->meeting_at ? \Carbon\Carbon::parse($order->meeting_at)->format('d.m.Y H:i') : '—' }}
                                </a>
                            </td>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ optional($order->measurement)->measured_at ? \Carbon\Carbon::parse($order->measurement->measured_at)->format('d.m.Y H:i') : '—' }}
                                </a>
                            </td>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ optional($order->contract)->signed_at ? \Carbon\Carbon::parse($order->contract->signed_at)->format('d.m.Y') : '—' }}
                                </a>
                            </td>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ optional($order->documentation)->completed_at ? \Carbon\Carbon::parse($order->documentation->completed_at)->format('d.m.Y') : '—' }}
                                </a>
                            </td>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ optional($order->installation)->installed_at ? \Carbon\Carbon::parse($order->installation->installed_at)->format('d.m.Y') : '—' }}
                                </a>
                            </td>
                            <td>
                                <a href="#" 
                                   class="order-id-link" 
                                   data-toggle="modal" 
                                   data-target="#showOrderModal{{ $order->id }}">
                                    {{ $order->production_stage }}
                                </a>
                            </td>
                            <td>
                                <form action="{{ route('employee.orders.destroy', $order) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100 my-3" onclick="return confirm('Вы уверены, что хотите удалить этот заказ?')">
                                        <i class="fe fe-trash"></i> Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Модальное окно для просмотра заказа -->
                        <div class="modal fade" id="showOrderModal{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="showOrderModalLabel{{ $order->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="showOrderModalLabel{{ $order->id }}">Просмотр заказа</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        @include('dashboard.orders.order-show', ['order' => $order,'attachments' => $order->getAllAttachmentsAttribute()])
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Модальное окно для редактирования заказа -->
                        <div class="modal fade" id="editOrderModal{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="editOrderModalLabel{{ $order->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editOrderModalLabel{{ $order->id }}">Редактировать заказ</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        @include('dashboard.orders.order-edit', ['order' => $order])
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Модальное окно для добавления медиа -->
                        <div class="modal fade" id="addAttachmentModal{{ $order->id }}" tabindex="-1" role="dialog" aria-labelledby="addAttachmentModalLabel{{ $order->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="addAttachmentModalLabel{{ $order->id }}">Добавить медиа</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="{{ route('employee.orders.attachMedia', $order) }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <input type="file" name="media[]" multiple class="form-control-file" id="media{{ $order->id }}">
                                            </div>
                                            <div class="form-group mt-3">
                                                <label for="comment{{ $order->id }}">Комментарий</label>
                                                <textarea name="comment" class="form-control" id="comment{{ $order->id }}" rows="3"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-3">Сохранить медиа</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

{{-- Календарь заказов --}}
<div class="modal fade" id="calendarModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Календарь заказов</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body p-0" id="calendarContent">
                <div id="calendar" data-type="order"></div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        let calendar;

        $('#calendarModal').on('shown.bs.modal', function () {
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) return;
            const type = calendarEl.dataset.type;

            // Получаем параметр manager_id из URL
            const urlParams = new URLSearchParams(window.location.search);
            const managerId = urlParams.get('manager_id');

            // Обновляем заголовок календаря с информацией о фильтре
            const modalTitle = $('#calendarModal .modal-title');
            let calendarTitle = 'Календарь заказов';
            
            // Получаем имя выбранного менеджера из соответствующей ссылки в dropdown
            if (managerId) {
                const activeDropdownItem = document.querySelector(`a.dropdown-item[href*="manager_id=${managerId}"]`);
                if (activeDropdownItem) {
                    // Извлекаем только текст, исключая иконки
                    let managerName = activeDropdownItem.textContent.trim();
                    // Убираем символ галочки если есть
                    managerName = managerName.replace(/\s*✓\s*$/, '').trim();
                    calendarTitle += ' - ' + managerName;
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

<!-- Модальное окно для создания заказа -->
<div class="modal fade" id="createOrderModal" tabindex="-1" role="dialog" aria-labelledby="createOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createOrderModalLabel">Создать новый заказ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @include('dashboard.orders.create-order')
            </div>
        </div>
    </div>
</div>
@endsection

@if (isset($editOrder) && $editOrder)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#editOrderModal{{ $editOrder->id }}').modal('show');
        });
    </script>
@endif

@if (isset($showOrderModal) && $showOrderModal && isset($order) && $order)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#showOrderModal{{ $order->id }}').modal('show');
        });
    </script>
@endif

<style>
/* Оптимизация стилей для компактной таблицы */
.table-orders th,
.table-orders td {
    padding: 0.1rem 0.2rem !important; /* Компактные строки */
    font-size: 1rem !important; /* Увеличенный размер шрифта */
    font-weight: 600 !important; /* Менее жирный текст */
    line-height: 1.2 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.table-orders td:last-child {
    white-space: normal !important;
    width: 120px !important; /* Ограничение ширины для кнопок */
}

.table-orders .btn {
    padding: 0.1rem 0.2rem !important; /* Компактные кнопки */
    font-size: 0.7rem !important;
}

/* Убрать лишнее пространство на больших экранах */
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

@media (max-width: 600px) {
    .d-flex.flex-wrap {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .d-flex.flex-wrap > * {
        width: 100% !important;
        margin-bottom: 0.5rem;
    }
}

/* --- ФИКС для выпадающего меню поверх таблицы --- */
.table-responsive {
    overflow: visible !important;
}
.dropdown-menu {
    z-index: 1055 !important;
}
</style>    

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех выпадающих меню
    $('.dropdown-toggle').dropdown();
    
    // Инициализация всех модальных окон
    $('.modal').modal({
        show: false
    });
});
</script>
