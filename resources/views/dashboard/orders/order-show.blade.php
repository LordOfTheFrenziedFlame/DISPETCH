    <div class="card my-4">
        <div class="card-header">
            <h3 class="card-title">Детали заказа</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Номер заказа</h5>
                    <p>{{ $order->order_number }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Клиент</h5>
                    <p>{{ $order->customer_name }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Наименование продукции</h5>
                    <p>{{ $order->product_name ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Комментарий заказа</h5>
                    <p>{{ $order->notes ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Комментарий замера</h5>
                    <p>{{ optional($order->measurement)->notes ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Комментарий договора</h5>
                    <p>{{ optional($order->contract)->comment ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Комментарий производства</h5>
                    <p>{{ optional($order->production)->notes ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Комментарий документации</h5>
                    <p>{{ optional($order->documentation)->notes ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Комментарий установки</h5>
                    <p>{{ optional($order->installation)->result_notes ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Адрес</h5>
                    <p>{{ $order->address }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Телефон</h5>
                    <p>{{ $order->phone_number }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Email</h5>
                    <p>{{ $order->email }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Менеджер</h5>
                    <p>{{ optional($order->manager)->name }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Замерщик</h5>
                    <p>{{ optional($order->surveyor)->name ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Конструктор</h5>
                    <p>{{ optional($order->constructor)->name ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Установщик</h5>
                    <p>{{ optional($order->installer)->name ?: '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Дата встречи</h5>
                    <p>{{ $order->meeting_at ? $order->meeting_at->format('d.m.Y H:i') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Дата замера</h5>
                    <p>{{ $order->measurement_at ? $order->measurement_at->format('d.m.Y H:i') : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Статус</h5>
                    <p>{{ $order->status }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Стоимость</h5>
                    <p>{{ $order->total_amount ? number_format($order->total_amount, 2, '.', ' ') . ' руб.' : '—' }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Продукция</h5>
                    <p>{{ $order->product_name ?: '—' }}</p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Вложения</h5>
                    <ul class="list-group">
                        @if(isset($attachments) && $attachments->isNotEmpty())
                            @foreach($attachments as $attachment)
                                <li class="list-group-item">
                                    <a href="{{ Storage::url($attachment->path) }}" target="_blank">{{ $attachment->filename }}</a>
                                    <p>{{ $attachment->comment }}</p>
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
            <a href="{{ route('employee.orders.edit', $order) }}" class="btn btn-outline-warning">Редактировать</a>
            <button type="button"
                    class="btn btn-outline-primary"
                    data-toggle="modal"
                    data-target="#addAttachmentModal{{ $order->id }}"
                    onclick="closeParentModal(this)">
                Добавить вложение
            </button>
            <a href="{{ route('employee.orders.index') }}" class="btn btn-outline-secondary">Назад к списку</a>
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
