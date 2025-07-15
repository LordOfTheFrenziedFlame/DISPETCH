<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Traits\HasRolePermissions;

class OrderController extends Controller
{
    use HasRolePermissions;

    /**
     * Получить данные для модального окна создания заказа
     */
    private function getCreateOrderData()
    {
        return [
            'managers' => \App\Models\User::where('role', 'manager')->get(['id', 'name']),
            'surveyors' => \App\Models\User::where('role', 'surveyor')->get(['id', 'name']),
            'constructors' => \App\Models\User::where('role', 'constructor')->get(['id', 'name']),
            'installers' => \App\Models\User::where('role', 'installer')->get(['id', 'name']),
            'allUsers' => \App\Models\User::all(['id', 'name', 'role']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$this->canViewOrders()) {
            return redirect()->back()->with('error', 'У вас нет доступа к заказам');
        }

        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $managerId = $request->query('manager_id');

        // Фильтрация заказов в зависимости от роли
        $ordersQuery = Order::with(['measurement', 'contract', 'documentation', 'installation', 'manager', 'surveyor', 'constructor', 'installer']);

        if ($this->isSurveyor()) {
            // Замерщик видит только назначенные ему заказы или заказы без назначенного замерщика
            $ordersQuery->where(function($query) {
                $query->where('surveyor_id', $this->getCurrentUser()->id)
                      ->orWhereNull('surveyor_id');
            });
        } elseif ($this->isConstructor()) {
            // Конструктор видит заказы в работе
            $ordersQuery->whereIn('status', ['in_progress', 'completed']);
        } elseif ($this->isInstaller()) {
            // Установщик видит заказы с готовой документацией
            $ordersQuery->whereHas('documentation', function($query) {
                $query->whereNotNull('completed_at');
            });
        } elseif ($this->isManager() && $managerId) {
            // Менеджер может фильтровать по конкретному менеджеру
            $ordersQuery->where('manager_id', $managerId);
        }

        $orders = $ordersQuery->latest()->get();

        // Получаем данные для фильтрации
        $managers = \App\Models\User::where('role', 'manager')->get(['id', 'name']);
        $selectedManager = null;
        if ($managerId) {
            $selectedManager = $managers->firstWhere('id', $managerId);
        }

        return view('dashboard.orders.index', array_merge([
            'orders' => $orders,
            'month' => $month,
            'year' => $year,
            'model' => Order::class,
            'dateField' => 'meeting_at',
            'managers' => $managers,
            'selectedManager' => $selectedManager,
        ], $this->getCreateOrderData()));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!$this->canManageOrders()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию заказов');
        }

        return view('dashboard.orders.create', $this->getCreateOrderData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$this->canManageOrders()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию заказов');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'email' => 'required|email',
            'meeting_at' => 'nullable|date',
            'manager_id' => 'required|exists:users,id',
            'surveyor_id' => 'nullable|exists:users,id',
            'constructor_id' => 'nullable|exists:users,id',
            'installer_id' => 'nullable|exists:users,id',
            'order_number' => 'required|integer|unique:orders,order_number',
            'product_name' => 'nullable|string|max:255',
            'total_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validated) {
            $order = Order::create($validated);
            return redirect()->route('employee.orders.index')->with('success', 'Заказ успешно создан');
        }

        return redirect()->back()->withErrors($validated)->withInput();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!$this->canViewOrders()) {
            return redirect()->back()->with('error', 'У вас нет доступа к просмотру заказов');
        }

        // Используем новый метод для правильного eager loading всех вложений
        $order = Order::withAllAttachments()->findOrFail($id);

        // Дополнительная проверка для ограниченных ролей
        if ($this->isSurveyor()) {
            if ($order->surveyor_id !== null && $order->surveyor_id !== $this->getCurrentUser()->id) {
                return redirect()->back()->with('error', 'У вас нет доступа к этому заказу');
            }
        }

        $attachments = $order->all_attachments;

        return view('dashboard.order-show', compact('order', 'attachments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!$this->canManageOrders()) {
            return redirect()->back()->with('error', 'У вас нет доступа к редактированию заказов');
        }

        $order = Order::findOrFail($id);
        
        return view('dashboard.orders.index', array_merge([
            'orders' => Order::with(['measurement', 'contract', 'documentation', 'installation', 'manager', 'surveyor', 'constructor', 'installer'])->latest()->get(),
            'editOrder' => $order,
        ], $this->getCreateOrderData()));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!$this->canManageOrders()) {
            return redirect()->back()->with('error', 'У вас нет доступа к редактированию заказов');
        }

        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'email' => 'required|email',
            'meeting_at' => 'nullable|date',
            'measurement_at' => 'nullable|date',
            'order_number' => 'required|integer|unique:orders,order_number,' . $order->id,
            'surveyor_id' => 'nullable|exists:users,id',
            'constructor_id' => 'nullable|exists:users,id',
            'installer_id' => 'nullable|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,in_progress,completed',
            'product_name' => 'nullable|string|max:255',
            'total_amount' => 'nullable|numeric|min:0',
        ]);
        

        if ($validated) {
            $order->update($validated);
            return redirect()->route('employee.orders.index')->with('success', 'Заказ успешно обновлен');
        }

        return redirect()->back()->withErrors($validated)->withInput();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->canManageOrders()) {
            return redirect()->back()->with('error', 'У вас нет доступа к удалению заказов');
        }

        $order = Order::findOrFail($id);
        $order->delete();
        return redirect()->route('employee.orders.index')->with('success', 'Заказ успешно удален');
    }

    public function orderByNumber(Request $request)
    {
        if (!$this->canManageOrders()) {
            return redirect()->back()->with('error', 'У вас нет доступа к просмотру заказов');
        }

        $order_number = $request->input('order_number');
        $order = Order::where('order_number', $order_number)->first();

        // Если заказ не найден — вернуть с ошибкой
        if (!$order) {
            return redirect()->route('employee.orders.index')->with('error', 'Заказ с таким номером не найден');
        }

        // Показываем только найденный заказ
        return view('dashboard.orders.index', array_merge([
            'orders' => collect([$order]), // только найденный заказ
            'showOrderModal' => true,
            'order' => $order,
        ], $this->getCreateOrderData()));
    }
}
