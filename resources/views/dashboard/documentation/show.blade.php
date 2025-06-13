@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Детали документации</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Заказ</h5>
                    <p>Заказ #{{ $documentation->order->order_number }} - {{ $documentation->order->customer_name }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Конструктор</h5>
                    <p>{{ optional($documentation->constructor)->name ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Дата завершения</h5>
                    <p>{{ $documentation->completed_at ? $documentation->completed_at->format('d.m.Y H:i') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Создано</h5>
                    <p>{{ $documentation->created_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Обновлено</h5>
                    <p>{{ $documentation->updated_at->format('d.m.Y H:i') }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Статус</h5>
                    <p>
                        @if($documentation->completed_at)
                            <span class="badge bg-success">Завершено</span>
                        @else
                            <span class="badge bg-warning">В процессе</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Описание</h5>
                    <p>{{ $documentation->description ?: 'Нет описания' }}</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Вложения</h5>
                    <ul class="list-group">
                        @if($documentation->attachments && $documentation->attachments->isNotEmpty())
                            @foreach($documentation->attachments as $attachment)
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
            <a href="{{ route('employee.documentations.edit', $documentation) }}" class="btn btn-outline-warning">Редактировать</a>
            <button type="button"
                    class="btn btn-outline-primary"
                    data-toggle="modal"
                    data-target="#addAttachmentModal{{ $documentation->id }}">
                Добавить вложение
            </button>
            <a href="{{ route('employee.documentations.index') }}" class="btn btn-outline-secondary">Назад к списку</a>
        </div>
    </div>
</div>
@endsection 