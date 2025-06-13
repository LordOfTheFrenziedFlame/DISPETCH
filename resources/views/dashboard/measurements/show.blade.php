@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Детали замера</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Заказ</h5>
                    <p>Заказ #{{ $measurement->order->order_number }} - {{ $measurement->order->customer_name }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Замерщик</h5>
                    <p>{{ optional($measurement->surveyor)->name ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Дата замера</h5>
                    <p>{{ $measurement->measured_at ? $measurement->measured_at->format('d.m.Y H:i') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Дата встречи (первичная)</h5>
                    <p>{{ $measurement->initial_meeting_at ? $measurement->initial_meeting_at->format('d.m.Y H:i') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Статус</h5>
                    <p>
                        @if($measurement->status === 'pending')
                            <span class="badge bg-warning">Ожидает</span>
                        @elseif($measurement->status === 'completed')
                            <span class="badge bg-success">Завершен</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <h5>Дата загрузки</h5>
                    <p>{{ $measurement->uploaded ? $measurement->uploaded->format('d.m.Y H:i') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Создан</h5>
                    <p>{{ $measurement->created_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Заметки</h5>
                    <p>{{ $measurement->notes ?: 'Нет заметок' }}</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Вложения</h5>
                    <ul class="list-group">
                        @if($measurement->attachments && $measurement->attachments->isNotEmpty())
                            @foreach($measurement->attachments as $attachment)
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
            <a href="{{ route('employee.measurements.edit', $measurement) }}" class="btn btn-outline-warning">Редактировать</a>
            <button type="button"
                    class="btn btn-outline-primary"
                    data-toggle="modal"
                    data-target="#addAttachmentModal{{ $measurement->id }}">
                Добавить вложение
            </button>
            <a href="{{ route('employee.measurements.index') }}" class="btn btn-outline-secondary">Назад к списку</a>
        </div>
    </div>
</div>
@endsection 