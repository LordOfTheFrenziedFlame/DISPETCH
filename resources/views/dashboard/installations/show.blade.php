@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Детали монтажа</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Заказ</h5>
                    <p>Заказ #{{ $installation->order->order_number }} - {{ $installation->order->customer_name }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Установщик</h5>
                    <p>{{ optional($installation->installer)->name ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Документация</h5>
                    <p>
                        @if($installation->documentation)
                            <a href="{{ route('employee.documentations.show', $installation->documentation) }}">
                                {{ Str::limit($installation->documentation->description, 50) }}
                            </a>
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <h5>Дата установки</h5>
                    <p>{{ $installation->installed_at ? $installation->installed_at->format('d.m.Y H:i') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Создан</h5>
                    <p>{{ $installation->created_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Обновлен</h5>
                    <p>{{ $installation->updated_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Статус</h5>
                    <p>
                        @if($installation->installed_at)
                            <span class="badge bg-success">Установлено</span>
                        @else
                            <span class="badge bg-warning">Ожидает установки</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Заметки по результату</h5>
                    <p>{{ $installation->result_notes ?: 'Нет заметок' }}</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Вложения по заказу</h5>
                    @include('dashboard.partials.attachments-list', ['attachments' => $installation->order->all_attachments])
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('employee.installations.edit', $installation) }}" class="btn btn-outline-warning">Редактировать</a>
            <button type="button"
                    class="btn btn-outline-primary"
                    data-toggle="modal"
                    data-target="#addAttachmentModal{{ $installation->id }}">
                Добавить вложение
            </button>
            <a href="{{ route('employee.installations.index') }}" class="btn btn-outline-secondary">Назад к списку</a>
        </div>
    </div>
</div>
@endsection 