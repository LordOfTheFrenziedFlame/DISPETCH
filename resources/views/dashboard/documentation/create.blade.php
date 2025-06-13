@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Создать новую документацию</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employee.documentations.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Заказ</label>
                    <select class="form-select" name="order_id" required>
                        <option value="">Выберите заказ</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">
                                Заказ #{{ $order->order_number }} - {{ $order->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Конструктор</label>
                    <select class="form-select" name="constructor_id" required>
                        <option value="">Выберите конструктора</option>
                        @foreach($constructors as $constructor)
                            <option value="{{ $constructor->id }}">{{ $constructor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Описание</label>
                    <textarea class="form-control" name="description" rows="6" placeholder="Введите описание документации" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Дата завершения</label>
                    <input type="datetime-local" class="form-control" name="completed_at">
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Создать документацию</button>
                    <a href="{{ route('employee.documentations.index') }}" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 