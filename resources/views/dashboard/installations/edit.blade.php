@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Редактировать монтаж</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employee.installations.update', $installation) }}">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label for="order_id">Заказ</label>
                    <select class="form-control" id="order_id" name="order_id" required>
                        @foreach(\App\Models\Order::all() as $order)
                            <option value="{{ $order->id }}" {{ $order->id == old('order_id', $installation->order_id) ? 'selected' : '' }}>
                                Заказ #{{ $order->order_number }} - {{ $order->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="documentation_id">Документация</label>
                    <select class="form-control" id="documentation_id" name="documentation_id">
                        <option value="">Выберите документацию</option>
                        @foreach(\App\Models\Documentation::with('order')->get() as $documentation)
                            <option value="{{ $documentation->id }}" {{ $documentation->id == old('documentation_id', $installation->documentation_id) ? 'selected' : '' }}>
                                Заказ #{{ $documentation->order->order_number }} - {{ Str::limit($documentation->description, 50) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="installer_id">Установщик</label>
                    <select class="form-control" id="installer_id" name="installer_id">
                        <option value="">Выберите установщика</option>
                        @foreach(\App\Models\User::where('role', 'installer')->get() as $installer)
                            <option value="{{ $installer->id }}" {{ $installer->id == old('installer_id', $installation->installer_id) ? 'selected' : '' }}>
                                {{ $installer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="installed_at">Дата установки</label>
                    <input type="datetime-local" class="form-control" id="installed_at" name="installed_at" 
                           value="{{ old('installed_at', optional($installation->installed_at)->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="form-group">
                    <label for="result_notes">Заметки по результату</label>
                    <textarea class="form-control" id="result_notes" name="result_notes" rows="4">{{ old('result_notes', $installation->result_notes) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="{{ route('employee.installations.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
</div>
@endsection 