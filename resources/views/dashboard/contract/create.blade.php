@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Создать новый договор</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employee.contracts.store') }}">
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
                    <label class="form-label">Номер договора</label>
                    <input type="text" class="form-control" name="contract_number" placeholder="Введите номер договора (опционально)">
                </div>
                <div class="mb-3">
                    <label class="form-label">Дата подписания</label>
                    <input type="date" class="form-control" name="signed_at">
                </div>
                <div class="mb-3">
                    <label class="form-label">Комментарий</label>
                    <textarea class="form-control" name="comment" rows="4" placeholder="Введите комментарий к договору"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Сумма договора, ₽</label>
                    <input type="number" step="0.01" class="form-control" name="final_amount" placeholder="Введите итоговую сумму">
                </div>
                <div class="mb-3">
                    <label class="form-label">Срок подготовки документации</label>
                    <input type="date" class="form-control" name="documentation_due_at">
                </div>
                <div class="mb-3">
                    <label class="form-label">Дата установки</label>
                    <input type="date" class="form-control" name="installation_date">
                </div>
                <div class="mb-3">
                    <label class="form-label">Установщик</label>
                    <select class="form-select" name="installer_id">
                        <option value="">Выберите установщика</option>
                        @foreach($installers as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Вид товара</label>
                    <input type="text" class="form-control" name="product_type" placeholder="Введите вид товара">
                </div>
                <div class="mb-3">
                    <label class="form-label">Дата готовности</label>
                    <input type="date" class="form-control" name="ready_date">
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Создать договор</button>
                    <a href="{{ route('employee.contracts.index') }}" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 