@php
    use Carbon\Carbon;

    $date = Carbon::create($year, $month, 1);
    $start = $date->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
    $end = $date->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
    $current = $start->copy();
@endphp

<div class="d-flex justify-content-between mb-2">
    <button class="btn btn-sm btn-outline-primary calendar-nav"
            data-month="{{ $date->copy()->subMonth()->format('m') }}"
            data-year="{{ $date->copy()->subMonth()->format('Y') }}">
        ←
    </button>

    <h4 class="m-0">{{ $date->locale('ru')->translatedFormat('F Y') }}</h4>

    <button class="btn btn-sm btn-outline-primary calendar-nav"
            data-month="{{ $date->copy()->addMonth()->format('m') }}"
            data-year="{{ $date->copy()->addMonth()->format('Y') }}">
        →
    </button>
</div>

<div class="calendar-wrapper">
    <table class="table table-bordered text-center calendar-table">
        <thead class="thead-light">
            <tr>
                @foreach (['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'] as $day)
                    <th>{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @while ($current <= $end)
                <tr>
                    @for ($i = 0; $i < 7; $i++)
                        @php
                            $dayStr = $current->format('Y-m-d');
                            $hasItem = isset($grouped[$dayStr]);
                        @endphp
                        <td class="{{ $hasItem ? 'bg-success text-white' : '' }}">
                            <div class="day-label">{{ $current->day }}</div>
                            @if ($hasItem)
                                <ul class="list-unstyled small mt-1 mb-0">
                                    @foreach ($grouped[$dayStr] as $item)
                                        <li>
                                            @if (class_basename($model) === 'Order')
                                                Заказ №{{ $item->id }} — {{ $item->customer_name }}
                                            @elseif (class_basename($model) === 'Measurement')
                                                Замер №{{ $item->id }} — {{ optional($item->order)->customer_name }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        @php $current->addDay(); @endphp
                    @endfor
                </tr>
            @endwhile
        </tbody>
    </table>
</div>

<style>
.calendar-table td {
    height: 100px;
    vertical-align: top;
    padding: 5px;
    position: relative;
}

.calendar-table .day-label {
    font-weight: bold;
}

.calendar-table .bg-success {
    background-color: #28a745 !important;
    color: white;
}

.calendar-table {
    table-layout: fixed;
    width: 100%;
}

.calendar-wrapper {
    min-height: 400px;
}
</style>

<script>
    $(document).on('click', '.calendar-nav', function() {
        let month = $(this).data('month');
        let year = $(this).data('year');

        $.get('{{ route('employee.measurements.calendar.partial') }}', {
            model: '{{ addslashes(\App\Models\Measurement::class) }}',
            dateField: 'measured_at',
            month: month,
            year: year
        }, function(data) {
            const calendarContent = document.getElementById('calendarContent');
            if (calendarContent) {
                calendarContent.innerHTML = data;
            } else {
                console.error('Element with ID "calendarContent" not found.');
            }
        }).fail(function() {
            alert('Ошибка загрузки данных календаря. Пожалуйста, попробуйте еще раз.');
        });
    });
</script>

@foreach ($grouped as $date => $items)
    <p>{{ $date }} → {{ $items->count() }} шт.</p>
@endforeach
