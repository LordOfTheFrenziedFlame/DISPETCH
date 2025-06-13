@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Редактировать документацию</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employee.documentations.update', $documentation) }}">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label for="order_id">Заказ</label>
                    <select class="form-control" id="order_id" name="order_id" required>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}" {{ $order->id == old('order_id', $documentation->order_id) ? 'selected' : '' }}>
                                Заказ #{{ $order->order_number }} - {{ $order->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="constructor_id">Конструктор</label>
                    <select class="form-control" id="constructor_id" name="constructor_id" required>
                        @foreach($constructors as $constructor)
                            <option value="{{ $constructor->id }}" {{ $constructor->id == old('constructor_id', $documentation->constructor_id) ? 'selected' : '' }}>
                                {{ $constructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea class="form-control" id="description" name="description" rows="6" required>{{ old('description', $documentation->description) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="completed_at">Дата завершения</label>
                    <input type="datetime-local" class="form-control" id="completed_at" name="completed_at" 
                           value="{{ old('completed_at', optional($documentation->completed_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="{{ route('employee.documentations.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
</div>
@endsection 