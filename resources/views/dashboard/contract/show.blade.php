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
                    <h5>Номер договора</h5>
                    <p>{{ $contract->contract_number }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Заказ</h5>
                    <p>№ {{ $contract->order->order_number }} – {{ $contract->order->customer_name }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Конструктор</h5>
                    <p>{{ optional($contract->constructor)->name ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Дата подписания</h5>
                    <p>{{ $contract->signed_at ? $contract->signed_at->format('d.m.Y') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Сумма договора</h5>
                    <p>{{ $contract->final_amount ? number_format($contract->final_amount, 2, '.', ' ') . ' руб.' : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Срок подготовки документации</h5>
                    <p>{{ $contract->documentation_due_at ? $contract->documentation_due_at->format('d.m.Y') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Дата установки</h5>
                    <p>{{ $contract->installation_date ? $contract->installation_date->format('d.m.Y') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Вид товара</h5>
                    <p>{{ $contract->product_type ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Дата готовности</h5>
                    <p>{{ $contract->ready_date ? $contract->ready_date->format('d.m.Y') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Монтажник</h5>
                    <p>{{ optional($contract->order->installer)->name ?: '—' }}</p>
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
                    <h5>Вложения по заказу</h5>
                    @include('dashboard.partials.attachments-list', ['attachments' => $contract->order->all_attachments])
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('employee.contracts.edit', $contract) }}" class="btn btn-outline-warning">Редактировать</a>
            <button type="button"
                    class="btn btn-outline-primary"
                    data-toggle="modal"
                    data-target="#addAttachmentModal{{ $contract->id }}"
                    onclick="closeParentModal(this)">
                Добавить вложение
            </button>
            <a href="{{ route('employee.contracts.index') }}" class="btn btn-outline-secondary">Назад к списку</a>
        </div>
    </div>
</div>

<script>
function closeParentModal(button) {
    var parentModal = $(button).closest('.modal');
    if (parentModal.length) {
        parentModal.modal('hide');
    }
}
</script>
@endsection 