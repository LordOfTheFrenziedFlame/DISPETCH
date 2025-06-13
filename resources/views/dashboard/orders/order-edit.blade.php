<form method="POST" action="{{ route('employee.orders.update', $order) }}">
    @csrf
    @method('PATCH')
    <div class="form-group">
        <label for="customer_name">Имя клиента</label>
        <input type="text" class="form-control" id="customer_name" name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" required>
    </div>
    <div class="form-group">
        <label for="address">Адрес</label>
        <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $order->address) }}" required>
    </div>
    <div class="form-group">
        <label for="phone_number">Телефон</label>
        <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $order->phone_number) }}" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $order->email) }}" required>
    </div>
    <div class="form-group">
        <label for="meeting_at">Дата встречи</label>
        <input type="datetime-local" class="form-control" id="meeting_at" name="meeting_at" value="{{ old('meeting_at', optional($order->meeting_at)->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="form-group">
        <label for="order_number">Номер заказа</label>
        <input type="number" class="form-control" id="order_number" name="order_number" value="{{ old('order_number', $order->order_number) }}" required>
    </div>
    <div class="form-group">
        <label for="total_amount">Стоимость</label>
        <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" value="{{ old('total_amount', $order->total_amount) }}">
    </div>
    <div class="form-group">
        <label for="manager_id">Менеджер</label>
        <select class="form-control" id="manager_id" name="manager_id" required>
            <!-- Assuming you have a list of managers to populate here -->
            @foreach(\App\Models\User::where('role', 'manager')->get() as $manager)
                <option value="{{ $manager->id }}" {{ $manager->id == old('manager_id', $order->manager_id) ? 'selected' : '' }}>
                    {{ $manager->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="surveyor_id">Замерщик</label>
        <select class="form-control" id="surveyor_id" name="surveyor_id">
            <option value="">Выберите замерщика</option>
            @if(auth('employees')->user()->role === 'manager')
                @foreach(\App\Models\User::all() as $user)
                    <option value="{{ $user->id }}" {{ $user->id == old('surveyor_id', $order->surveyor_id) ? 'selected' : '' }}>
                        {{ $user->name }} ({{ ucfirst($user->role) }})
                    </option>
                @endforeach
            @else
                @foreach(\App\Models\User::where('role', 'surveyor')->get() as $surveyor)
                    <option value="{{ $surveyor->id }}" {{ $surveyor->id == old('surveyor_id', $order->surveyor_id) ? 'selected' : '' }}>
                        {{ $surveyor->name }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="form-group">
        <label for="constructor_id">Конструктор</label>
        <select class="form-control" id="constructor_id" name="constructor_id">
            <option value="">Выберите конструктора</option>
            @if(auth('employees')->user()->role === 'manager')
                @foreach(\App\Models\User::all() as $user)
                    <option value="{{ $user->id }}" {{ $user->id == old('constructor_id', $order->constructor_id) ? 'selected' : '' }}>
                        {{ $user->name }} ({{ ucfirst($user->role) }})
                    </option>
                @endforeach
            @else
                @foreach(\App\Models\User::where('role', 'constructor')->get() as $constructor)
                    <option value="{{ $constructor->id }}" {{ $constructor->id == old('constructor_id', $order->constructor_id) ? 'selected' : '' }}>
                        {{ $constructor->name }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="form-group">
        <label for="installer_id">Установщик</label>
        <select class="form-control" id="installer_id" name="installer_id">
            <option value="">Выберите установщика</option>
            @if(auth('employees')->user()->role === 'manager')
                @foreach(\App\Models\User::all() as $user)
                    <option value="{{ $user->id }}" {{ $user->id == old('installer_id', $order->installer_id) ? 'selected' : '' }}>
                        {{ $user->name }} ({{ ucfirst($user->role) }})
                    </option>
                @endforeach
            @else
                @foreach(\App\Models\User::where('role', 'installer')->get() as $installer)
                    <option value="{{ $installer->id }}" {{ $installer->id == old('installer_id', $order->installer_id) ? 'selected' : '' }}>
                        {{ $installer->name }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="form-group">
        <label for="status">Статус</label>
        <select class="form-control" id="status" name="status" required>
            <option value="in_progress" {{ old('status', $order->status) === 'in_progress' ? 'selected' : '' }}>В процессе</option>
            <option value="pending" {{ old('status', $order->status) === 'pending' ? 'selected' : '' }}>Приостановлен</option>
            <option value="completed" {{ old('status', $order->status) === 'completed' ? 'selected' : '' }}>Завершен</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
    <a href="{{ route('employee.orders.index') }}" class="btn btn-secondary">Отмена</a>
</form>
