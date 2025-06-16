<div class="card my-4">
    <div class="card-header">
        <h3 class="card-title">Создать новый заказ</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('employee.orders.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Имя клиента</label>
                <input type="text" class="form-control" name="customer_name" placeholder="Введите имя клиента" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Адрес</label>
                <input type="text" class="form-control" name="address" placeholder="Введите адрес" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Телефон</label>
                <input type="text" class="form-control" name="phone_number" placeholder="Введите номер телефона" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" placeholder="Введите email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Номер заказа</label>
                <input type="number" class="form-control" name="order_number" placeholder="Введите номер заказа" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Наименование продукции</label>
                <input type="text" class="form-control" name="product_name" placeholder="Введите наименование продукции">
            </div>
            <div class="mb-3">
                <label class="form-label">Стоимость</label>
                <input type="number" step="0.01" class="form-control" name="total_amount" placeholder="Введите стоимость заказа">
            </div>
            <div class="mb-3">
                <label class="form-label">Менеджер</label>
                <select class="form-select" name="manager_id" required>
                    <option value="">Выберите менеджера</option>
                    <!-- Assuming you have a $managers variable passed to the view -->
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Замерщик</label>
                <select class="form-select" name="surveyor_id">
                    <option value="">Выберите замерщика</option>
                    @foreach($surveyors as $surveyor)
                        <option value="{{ $surveyor->id }}">{{ $surveyor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Дата встречи</label>
                <input type="datetime-local" class="form-control" name="meeting_at" required>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Создать заказ</button>
            </div>
        </form>
    </div>
</div>
