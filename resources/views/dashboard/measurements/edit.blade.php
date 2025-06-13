@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Редактировать замер</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employee.measurements.update', $measurement) }}">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label for="order_id">Заказ</label>
                    <select class="form-control" id="order_id" name="order_id" required>
                        @foreach(\App\Models\Order::all() as $order)
                            <option value="{{ $order->id }}" {{ $order->id == old('order_id', $measurement->order_id) ? 'selected' : '' }}>
                                Заказ #{{ $order->order_number }} - {{ $order->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="surveyor_id">Замерщик</label>
                    <select class="form-control" id="surveyor_id" name="surveyor_id" required>
                        @foreach(\App\Models\User::where('role', 'surveyor')->get() as $surveyor)
                            <option value="{{ $surveyor->id }}" {{ $surveyor->id == old('surveyor_id', $measurement->surveyor_id) ? 'selected' : '' }}>
                                {{ $surveyor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="measured_at">Дата замера</label>
                    <input type="datetime-local" class="form-control" id="measured_at" name="measured_at" 
                           value="{{ old('measured_at', optional($measurement->measured_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="form-group">
                    <label for="initial_meeting_at">Дата встречи (первичная)</label>
                    <input type="datetime-local" class="form-control" id="initial_meeting_at" name="initial_meeting_at" 
                           value="{{ old('initial_meeting_at', optional($measurement->initial_meeting_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="form-group">
                    <label for="notes">Заметки</label>
                    <textarea class="form-control" id="notes" name="notes" rows="4">{{ old('notes', $measurement->notes) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="status">Статус</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="pending" {{ old('status', $measurement->status) === 'pending' ? 'selected' : '' }}>Ожидает</option>
                        <option value="completed" {{ old('status', $measurement->status) === 'completed' ? 'selected' : '' }}>Завершен</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="{{ route('employee.measurements.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
</div>
@endsection 