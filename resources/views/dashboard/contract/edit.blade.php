@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Редактировать договор</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employee.contracts.update', $contract) }}">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label for="order_id">Заказ</label>
                    <select class="form-control" id="order_id" name="order_id" required>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}" {{ $order->id == old('order_id', $contract->order_id) ? 'selected' : '' }}>
                                Заказ #{{ $order->order_number }} - {{ $order->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="constructor_id">Конструктор</label>
                    <select class="form-control" id="constructor_id" name="constructor_id" required>
                        @foreach($constructors as $constructor)
                            <option value="{{ $constructor->id }}" {{ $constructor->id == old('constructor_id', $contract->constructor_id) ? 'selected' : '' }}>
                                {{ $constructor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="contract_number">Номер договора</label>
                    <input type="text" class="form-control" id="contract_number" name="contract_number" value="{{ old('contract_number', $contract->contract_number) }}">
                </div>
                <div class="form-group">
                    <label for="signed_at">Дата подписания</label>
                    <input type="date" class="form-control" id="signed_at" name="signed_at" 
                           value="{{ old('signed_at', optional($contract->signed_at)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label for="comment">Комментарий</label>
                    <textarea class="form-control" id="comment" name="comment" rows="4">{{ old('comment', $contract->comment) }}</textarea>
                </div>
                <div class="form-group">
                    <label for="final_amount">Сумма договора, ₽</label>
                    <input type="number" step="0.01" class="form-control" id="final_amount" name="final_amount" value="{{ old('final_amount', $contract->final_amount) }}">
                </div>
                <div class="form-group">
                    <label for="documentation_due_at">Срок подготовки документации</label>
                    <input type="date" class="form-control" id="documentation_due_at" name="documentation_due_at" value="{{ old('documentation_due_at', optional($contract->documentation_due_at)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label for="installation_date">Дата установки</label>
                    <input type="date" class="form-control" id="installation_date" name="installation_date" value="{{ old('installation_date', optional($contract->installation_date)->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label for="product_type">Вид товара</label>
                    <input type="text" class="form-control" id="product_type" name="product_type" value="{{ old('product_type', $contract->product_type) }}">
                </div>
                <div class="form-group">
                    <label for="ready_date">Дата готовности</label>
                    <input type="date" class="form-control" id="ready_date" name="ready_date" value="{{ old('ready_date', optional($contract->ready_date)->format('Y-m-d')) }}">
                </div>
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="{{ route('employee.contracts.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
</div>
@endsection 