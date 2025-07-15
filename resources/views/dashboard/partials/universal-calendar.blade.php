{{-- Универсальный календарный компонент --}}
@php
    $calendarType = $calendarType ?? 'order';
    $modalId = 'calendarModal_' . $calendarType;
    $calendarId = 'calendar_' . $calendarType;
    $filterParam = $filterParam ?? 'employee_id';
    $currentFilterValue = request($filterParam);
    
    // Получаем информацию о фильтре
    $filterInfo = '';
    if ($currentFilterValue && isset($employees)) {
        $selectedEmployee = $employees->firstWhere('id', $currentFilterValue);
        if ($selectedEmployee) {
            $filterInfo = ' - ' . $selectedEmployee->name;
        }
    }
    
    // Заголовки для разных типов календарей
    $calendarTitles = [
        'order' => 'Календарь заказов',
        'measurement' => 'Календарь замеров',
        'documentation' => 'Календарь документации',
        'production' => 'Календарь производства',
        'installation' => 'Календарь установок',
        'contract' => 'Календарь договоров',
    ];
    
    $modalTitle = $calendarTitles[$calendarType] ?? 'Календарь';
@endphp

{{-- Кнопка календаря --}}
<button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#{{ $modalId }}">
    <i class="fe fe-calendar"></i> Календарь
    @if($filterInfo)
        <small>{{ $filterInfo }}</small>
    @endif
</button>

{{-- Модальное окно календаря --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog modal-fullscreen" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $modalTitle }}</h5>
                {{-- Кнопки фильтрации --}}
                <div class="btn-group mr-auto ml-3" id="calendarFilters_{{ $calendarType }}">
                    <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle" 
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fe fe-filter"></i> Фильтр по сотрудникам
                    </button>
                    <div class="dropdown-menu" id="employeeFilterDropdown_{{ $calendarType }}">
                        <a class="dropdown-item {{ !$currentFilterValue ? 'active' : '' }}" 
                           href="#" data-filter-value="" data-filter-name="Все сотрудники">
                            Все сотрудники
                        </a>
                        <div class="dropdown-divider"></div>
                        {{-- Список сотрудников будет загружен через JavaScript --}}
                    </div>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body p-0" id="calendarContent_{{ $calendarType }}">
                <div id="{{ $calendarId }}" data-type="{{ $calendarType }}"></div>
            </div>
        </div>
    </div>
</div>

{{-- Стили для календаря --}}
<style>
#{{ $calendarId }} {
    height: calc(100vh - 150px);
    min-height: 600px;
}

.fc-event-title {
    font-size: 0.85em;
    font-weight: 500;
}

.fc-daygrid-event {
    margin: 1px 0;
    border-radius: 3px;
}

.fc-event {
    cursor: pointer;
    transition: all 0.2s ease;
}

.fc-event:hover {
    opacity: 0.8;
    transform: scale(1.02);
}

.calendar-legend {
    padding: 10px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    font-size: 0.875rem;
}

.legend-item {
    display: inline-block;
    margin-right: 15px;
    margin-bottom: 5px;
}

.legend-color {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 2px;
    margin-right: 5px;
    vertical-align: middle;
}
</style>

{{-- JavaScript для календаря --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    let calendar_{{ str_replace('-', '_', $calendarType) }};
    let employees_{{ str_replace('-', '_', $calendarType) }} = [];
    let currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }} = null;

    // Загрузка списка сотрудников для фильтрации
    function loadEmployees_{{ str_replace('-', '_', $calendarType) }}() {
        fetch('/employee/calendar/employees?type={{ $calendarType === "order" ? "managers" : "all" }}')
            .then(response => response.json())
            .then(data => {
                employees_{{ str_replace('-', '_', $calendarType) }} = data;
                renderEmployeeDropdown_{{ str_replace('-', '_', $calendarType) }}();
            })
            .catch(error => {
                console.error('Ошибка загрузки сотрудников:', error);
            });
    }

    // Отрисовка выпадающего списка сотрудников
    function renderEmployeeDropdown_{{ str_replace('-', '_', $calendarType) }}() {
        const dropdown = document.getElementById('employeeFilterDropdown_{{ $calendarType }}');
        if (!dropdown) return;

        // Очищаем существующие элементы (кроме "Все сотрудники")
        const existingItems = dropdown.querySelectorAll('.employee-filter-item');
        existingItems.forEach(item => item.remove());

        // Добавляем сотрудников
        employees_{{ str_replace('-', '_', $calendarType) }}.forEach(employee => {
            const item = document.createElement('a');
            item.className = 'dropdown-item employee-filter-item';
            item.href = '#';
            item.dataset.filterValue = employee.id;
            item.dataset.filterName = employee.name;
            item.innerHTML = `${employee.name} <small class="text-muted">(${employee.role_display})</small>`;
            
            // Проверяем активность
            const urlParams = new URLSearchParams(window.location.search);
            const currentFilter = urlParams.get('{{ $filterParam }}') || urlParams.get('manager_id') || urlParams.get('currentUserMeasurements');
            if (currentFilter == employee.id) {
                item.classList.add('active');
                currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }} = employee.id;
            }

            dropdown.appendChild(item);
        });

        // Обработчики кликов по фильтрам
        dropdown.addEventListener('click', function(e) {
            if (e.target.classList.contains('dropdown-item')) {
                e.preventDefault();
                
                // Убираем активность со всех элементов
                dropdown.querySelectorAll('.dropdown-item').forEach(item => {
                    item.classList.remove('active');
                });
                
                // Добавляем активность на выбранный элемент
                e.target.classList.add('active');
                
                // Получаем значение фильтра
                const filterValue = e.target.dataset.filterValue;
                const filterName = e.target.dataset.filterName;
                
                currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }} = filterValue;
                
                // Обновляем заголовок календаря
                updateCalendarTitle_{{ str_replace('-', '_', $calendarType) }}(filterName);
                
                // Перезагружаем календарь с новым фильтром
                if (calendar_{{ str_replace('-', '_', $calendarType) }}) {
                    reloadCalendarEvents_{{ str_replace('-', '_', $calendarType) }}();
                }
            }
        });
    }

    // Обновление заголовка календаря
    function updateCalendarTitle_{{ str_replace('-', '_', $calendarType) }}(filterName) {
        const modalTitle = document.querySelector('#{{ $modalId }} .modal-title');
        if (modalTitle) {
            let title = '{{ $modalTitle }}';
            if (filterName && filterName !== 'Все сотрудники') {
                title += ' - ' + filterName;
            }
            modalTitle.textContent = title;
        }
    }

    // Перезагрузка событий календаря
    function reloadCalendarEvents_{{ str_replace('-', '_', $calendarType) }}() {
        if (calendar_{{ str_replace('-', '_', $calendarType) }}) {
            const extraParams = { 
                type: '{{ $calendarType }}' 
            };
            
            if (currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }}) {
                extraParams.employee_id = currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }};
                // Обратная совместимость
                @if($calendarType === 'order')
                extraParams.manager_id = currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }};
                @elseif($calendarType === 'measurement') 
                extraParams.currentUserMeasurements = currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }};
                @endif
            }

            // Обновляем источник событий
            calendar_{{ str_replace('-', '_', $calendarType) }}.getEventSources().forEach(source => {
                source.remove();
            });

            calendar_{{ str_replace('-', '_', $calendarType) }}.addEventSource({
                url: '/employee/calendar/events',
                method: 'GET',
                extraParams: extraParams,
                failure: function () {
                    alert('Ошибка загрузки событий!');
                },
            });
        }
    }

    // Инициализация календаря при открытии модального окна
    $('#{{ $modalId }}').on('shown.bs.modal', function () {
        // Проверяем доступность FullCalendar
        if (typeof window.FullCalendar === 'undefined') {
            console.error('FullCalendar не загружен');
            alert('Ошибка: FullCalendar не загружен. Пожалуйста, обновите страницу.');
            return;
        }

        // Очищаем содержимое
        $('#calendarContent_{{ $calendarType }}').html('<div id="{{ $calendarId }}" data-type="{{ $calendarType }}"></div>');
        
        const calendarEl = document.getElementById('{{ $calendarId }}');
        if (!calendarEl) {
            console.error('Элемент календаря не найден:', '{{ $calendarId }}');
            return;
        }

        // Загружаем сотрудников, если ещё не загружены
        if (employees_{{ str_replace('-', '_', $calendarType) }}.length === 0) {
            loadEmployees_{{ str_replace('-', '_', $calendarType) }}();
        }

        // Определяем начальные параметры фильтрации
        const urlParams = new URLSearchParams(window.location.search);
        const currentFilter = urlParams.get('{{ $filterParam }}') || urlParams.get('manager_id') || urlParams.get('currentUserMeasurements');
        currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }} = currentFilter;

        // Формируем extraParams
        const extraParams = { 
            type: '{{ $calendarType }}' 
        };
        
        if (currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }}) {
            extraParams.employee_id = currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }};
            // Обратная совместимость
            @if($calendarType === 'order')
            extraParams.manager_id = currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }};
            @elseif($calendarType === 'measurement') 
            extraParams.currentUserMeasurements = currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }};
            @endif
        }

        // Обновляем заголовок
        if (currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }}) {
            const selectedEmployee = employees_{{ str_replace('-', '_', $calendarType) }}.find(emp => emp.id == currentEmployeeFilter_{{ str_replace('-', '_', $calendarType) }});
            if (selectedEmployee) {
                updateCalendarTitle_{{ str_replace('-', '_', $calendarType) }}(selectedEmployee.name);
            }
        } else {
            updateCalendarTitle_{{ str_replace('-', '_', $calendarType) }}('Все сотрудники');
        }

        // Уничтожаем предыдущий календарь если есть
        if (calendar_{{ str_replace('-', '_', $calendarType) }}) {
            calendar_{{ str_replace('-', '_', $calendarType) }}.destroy();
        }

        try {
            // Создаём новый календарь
            calendar_{{ str_replace('-', '_', $calendarType) }} = new window.FullCalendar.Calendar(calendarEl, {
                plugins: [window.FullCalendar.dayGridPlugin],
                locale: window.FullCalendar.ruLocale || 'ru',
                initialView: 'dayGridMonth',
                height: '100%',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                events: {
                    url: '/employee/calendar/events',
                    method: 'GET',
                    extraParams: extraParams,
                    failure: function () {
                        alert('Ошибка загрузки событий!');
                    },
                },
                eventClick: function(info) {
                    // Обработка клика по событию
                    const eventProps = info.event.extendedProps;
                    if (eventProps.orderId) {
                        // Можно добавить переход к заказу или другие действия
                        console.log('Клик по событию:', eventProps);
                    }
                }
            });

            calendar_{{ str_replace('-', '_', $calendarType) }}.render();
        } catch (error) {
            console.error('Ошибка создания календаря:', error);
            alert('Ошибка инициализации календаря: ' + error.message);
        }
    });

    // Уничтожение календаря при закрытии модального окна
    $('#{{ $modalId }}').on('hidden.bs.modal', function () {
        if (calendar_{{ str_replace('-', '_', $calendarType) }}) {
            calendar_{{ str_replace('-', '_', $calendarType) }}.destroy();
            calendar_{{ str_replace('-', '_', $calendarType) }} = null;
        }
    });
});
</script>

{{-- Легенда цветов --}}
<div class="calendar-legend" style="display: none;" id="calendarLegend_{{ $calendarType }}">
    <strong>Условные обозначения:</strong>
    <span class="legend-item">
        <span class="legend-color" style="background-color: #4caf50;"></span>
        Выполнено/В работе
    </span>
    <span class="legend-item">
        <span class="legend-color" style="background-color: #e53935;"></span>
        Просрочено
    </span>
    <span class="legend-item">
        <span class="legend-color" style="background-color: #ffb300;"></span>
        Скоро дедлайн (до 3 дней)
    </span>
    <span class="legend-item">
        <span class="legend-color" style="background-color: #2196f3;"></span>
        В срок/Без дедлайна
    </span>
</div> 