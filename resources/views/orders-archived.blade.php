@extends('layouts.admin')

@section('title', 'Архив заказов')

@section('content')
<div class="card my-4">

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title fw-bold">Архив заказов</h3>
        <a href="{{ route('employee.orders.index') }}" class="btn btn-sm btn-outline-primary fw-bold">
            <i class="fe fe-arrow-left"></i> Назад
        </a>
    </div>

    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap table-archived">
            <thead>
                <tr>
                    <th class="fw-bold">№</th>
                    <th class="fw-bold">Клиент</th>
                    <th class="fw-bold">Адрес</th>
                    <th class="fw-bold">Удалён</th>
                    <th class="fw-bold">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                <tr>
                    <td class="fw-bold">{{ $order->id }}</td>
                    <td class="fw-bold">{{ $order->customer_name }}</td>
                    <td class="fw-bold">{{ $order->address }}</td>
                    <td class="fw-bold">{{ $order->deleted_at->format('d.m.Y H:i') }}</td>
                    <td>
                        <div class="d-flex flex-column gap-0">
                            <form method="POST" action="{{ route('employee.archived.restore', $order->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success fw-bold w-100">Вернуть</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-info fw-bold w-100" data-toggle="modal" data-target="#historyModal{{ $order->id }}">
                                История
                            </button>
                        </div>
                        </div>
                    </td>
                </tr>

                <!-- Модалка История -->
                <div class="modal fade" id="historyModal{{ $order->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">История заказа №{{ $order->id }}</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <h6 class="fw-bold">Замер:</h6>
                                @if ($order->measurement)
                                <div class="d-flex flex-column gap-2">
                                    <div>
                                        <span class="fw-bold">Дата замера:</span> {{ $order->measurement->measured_at ? \Carbon\Carbon::parse($order->measurement->measured_at)->format('d.m.Y H:i') : '—' }}
                                    </div>
                                    <div>
                                        <span class="fw-bold">Примечание:</span> {{ $order->measurement->notes ?? 'Нет примечаний' }}
                                    </div>
                                    <div> <span class="fw-bold">Номер заявки:{{ $order->order_number }}
                                    </span> 
                                    </div>
                                    <div>
                                        <span class="fw-bold">Медиа:</span>
                                        <ul>
                                            @foreach ($order->measurement->attachments as $attachment)
                                                <li><a href="{{ Storage::url($attachment->path) }}" target="_blank">{{ $attachment->filename }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @if ($order->measurement->trashed() && Auth::user('employees')->role === 'manager')
                                        <form method="POST" action="{{ route('employee.measurements.restore', $order->measurement->id) }}" class="mt-2">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-warning fw-bold">Восстановить замер</button>
                                        </form>
                                    @endif
                                </div>
                                @else
                                    <div class="fw-bold">Нет замера</div>
                                @endif

                                {{-- Здесь можно добавить документы, установку и т.п. --}}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

<style>
.table-archived th,
.table-archived td {
    padding: 0.1rem 0.2rem !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    line-height: 1.2 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.table-archived td:last-child {
    white-space: normal !important;
    width: 120px !important;
}

.table-archived .btn {
    padding: 0.1rem 0.2rem !important;
    font-size: 0.7rem !important;
    font-weight: 600 !important;
}

.table-archived .d-flex.flex-column > * {
    margin-bottom: 0 !important;
    margin-top: 0 !important;
}

@media (min-width: 992px) {
    .table-responsive {
        overflow-x: hidden;
    }
}
</style>
