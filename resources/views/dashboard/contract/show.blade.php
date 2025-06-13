@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Детали договора</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Заказ</h5>
                    <p>Заказ #{{ $contract->order->order_number }} - {{ $contract->order->customer_name }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Конструктор</h5>
                    <p>{{ optional($contract->constructor)->name ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Номер договора</h5>
                    <p>{{ $contract->contract_number }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Дата подписания</h5>
                    <p>{{ $contract->signed_at ? $contract->signed_at->format('d.m.Y') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Создан</h5>
                    <p>{{ $contract->created_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Обновлен</h5>
                    <p>{{ $contract->updated_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Комментарий</h5>
                    <p>{{ $contract->comment ?: 'Нет комментария' }}</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Вложения</h5>
                    <ul class="list-group">
                        @if($contract->attachments && $contract->attachments->isNotEmpty())
                            @foreach($contract->attachments as $attachment)
                                <li class="list-group-item">
                                    <a href="{{ Storage::url($attachment->path) }}" target="_blank">{{ $attachment->filename }}</a>
                                    @if($attachment->comment)
                                        <p class="text-muted">{{ $attachment->comment }}</p>
                                    @endif
                                </li>
                            @endforeach
                        @else
                            <li class="list-group-item">Нет вложений</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('employee.contracts.edit', $contract) }}" class="btn btn-outline-warning">Редактировать</a>
            <button type="button"
                    class="btn btn-outline-primary"
                    data-toggle="modal"
                    data-target="#addAttachmentModal{{ $contract->id }}">
                Добавить вложение
            </button>
            <a href="{{ route('employee.contracts.index') }}" class="btn btn-outline-secondary">Назад к списку</a>
        </div>
    </div>
</div>
@endsection 