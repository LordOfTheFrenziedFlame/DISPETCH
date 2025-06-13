@extends('layouts.admin')

@section('title', 'Календарь')

@section('content')
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Календарь</h3>
        </div>
        <div class="card-body">
            <div id="calendar" data-type="order"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const type = calendarEl.dataset.type || 'order';
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'ru',
            events: {
                url: '/api/calendar',
                method: 'GET',
                extraParams: { type: type },
                failure: function () {
                    alert('Ошибка загрузки событий!');
                },
            },
        });
        calendar.render();
    });
</script>
@endpush 