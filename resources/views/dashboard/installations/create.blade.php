@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Создать новый монтаж</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employee.installations.store') }}">
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
                    <label class="form-label">Документация</label>
                    <select class="form-select" name="documentation_id">
                        <option value="">Выберите документацию</option>
                        @foreach(\App\Models\Documentation::with('order')->get() as $documentation)
                            <option value="{{ $documentation->id }}">
                                Заказ #{{ $documentation->order->order_number }} - {{ Str::limit($documentation->description, 50) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Установщик</label>
                    <select class="form-select" name="installer_id">
                        <option value="">Выберите установщика</option>
                        @foreach($installers as $installer)
                            <option value="{{ $installer->id }}">{{ $installer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Дата установки</label>
                    <input type="datetime-local" class="form-control" name="installed_at">
                </div>
                <div class="mb-3">
                    <label class="form-label">Заметки по результату</label>
                    <textarea class="form-control" name="result_notes" rows="4" placeholder="Введите заметки по результату монтажа"></textarea>
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Создать монтаж</button>
                    <a href="{{ route('employee.installations.index') }}" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 