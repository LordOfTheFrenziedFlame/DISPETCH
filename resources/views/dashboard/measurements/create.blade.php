@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Создать новый замер</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employee.measurements.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Заказ</label>
                    <select name="order_id" class="form-select" required>
                        <option value="">Выберите заказ</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">
                                Заказ #{{ $order->order_number }} - {{ $order->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Замерщик</label>
                    <select name="surveyor_id" class="form-select" required>
                        <option value="">Выберите замерщика</option>
                        @foreach($surveyors as $surveyor)
                            <option value="{{ $surveyor->id }}">{{ $surveyor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Дата замера</label>
                    <input type="datetime-local" class="form-control" name="measured_at" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Дата встречи (первичная)</label>
                    <input type="datetime-local" class="form-control" name="initial_meeting_at">
                </div>
                <div class="mb-3">
                    <label class="form-label">Заметки</label>
                    <textarea class="form-control" name="notes" rows="4" placeholder="Введите заметки по замеру"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Статус</label>
                    <select class="form-select" name="status" required>
                        <option value="pending">Ожидает</option>
                        <option value="completed">Завершен</option>
                    </select>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Создать замер</button>
                    <a href="{{ route('employee.measurements.index') }}" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 