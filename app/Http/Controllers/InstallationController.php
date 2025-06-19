<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Installation;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasRolePermissions;

class InstallationController extends Controller
{
    use HasRolePermissions;

    private function canViewInstallations()
    {
        $role = Auth::guard('employees')->user()->role;
        return in_array($role, ['manager', 'installer', 'constructor']);
    }

    private function canManageInstallations()
    {
        $role = Auth::guard('employees')->user()->role;
        return in_array($role, ['manager', 'installer']);
    }

    private function canConfirmInstallations()
    {
        $role = Auth::guard('employees')->user()->role;
        return in_array($role, ['manager', 'installer']);
    }

    public function index(Request $request)
    {
        if (!$this->canViewInstallations()) {
            return redirect()->route('employee.orders.index');
        }

        $user = $this->getCurrentUser();
        
        // Базовый запрос с фильтрацией по неудаленным заказам (убираем фильтр по статусу)
        $query = Installation::with(['order', 'installer', 'documentation'])
            ->whereHas('order', function ($query) {
                $query->whereNull('deleted_at');
            });

        // Фильтрация установок в зависимости от роли
        if ($this->isManager()) {
            // Менеджер видит все установки, может фильтровать по конкретному сотруднику
            if ($request->has('manager_id')) {
                $employeeId = $request->input('manager_id');
                $query->whereHas('order', function ($q) use ($employeeId) {
                    $q->where(function ($subQ) use ($employeeId) {
                        $subQ->where('manager_id', $employeeId)
                             ->orWhere('surveyor_id', $employeeId)
                             ->orWhere('constructor_id', $employeeId)
                             ->orWhere('installer_id', $employeeId);
                    });
                })->orWhere('installer_id', $employeeId);
            }
        } elseif ($this->isConstructor()) {
            // Конструктор видит установки, связанные с его документацией
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('constructor_id', $user->id);
            });
        } elseif ($this->isInstaller()) {
            // Установщик видит только свои установки
            $query->where('installer_id', $user->id);
        }

        $installations = $query->latest()->get();
        $employees = \App\Models\User::all(['id', 'name', 'role']);
        $selectedEmployee = $request->get('manager_id') ? \App\Models\User::find($request->get('manager_id')) : null;
        
        return view('dashboard.installations.index', compact('installations', 'employees', 'selectedEmployee'));
    }

    public function create()
    {
        if (!$this->canManageInstallations()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию установок');
        }
        
        $orders = \App\Models\Order::where('status', '!=', 'completed')->get(['id', 'order_number', 'customer_name']);
        $installers = \App\Models\User::where('role', 'installer')->get(['id', 'name']);
        
        return view('dashboard.installations.create', compact('orders', 'installers'));
    }

    public function store(Request $request)
    {
        if (!$this->canManageInstallations()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию установок');
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'documentation_id' => 'nullable|exists:documentations,id',
            'installer_id' => 'nullable|exists:users,id',
            'installed_at' => 'nullable|date',
            'result_notes' => 'nullable|string|max:1000',
        ]);

        Installation::create($request->all());
        return redirect()->route('employee.installations.index')->with('success', 'Установка успешно создана');
    }

    public function show(Installation $installation)
    {
        if (!$this->canViewInstallations()) {
            return redirect()->back()->with('error', 'У вас нет доступа к просмотру установок');
        }
        return view('dashboard.installations.show', compact('installation'));
    }

    public function edit(Installation $installation)
    {
        if (!$this->canManageInstallations()) {
            return redirect()->back()->with('error', 'У вас нет доступа к редактированию установок');
        }
        
        $orders = \App\Models\Order::all(['id', 'order_number', 'customer_name']);
        $installers = \App\Models\User::where('role', 'installer')->get(['id', 'name']);
        
        return view('dashboard.installations.edit', compact('installation', 'orders', 'installers'));
    }

    public function update(Request $request, Installation $installation)
    {
        if (!$this->canManageInstallations()) {
            return redirect()->back()->with('error', 'У вас нет доступа к обновлению установок');
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'documentation_id' => 'nullable|exists:documentations,id',
            'installer_id' => 'nullable|exists:users,id',
            'installed_at' => 'nullable|date',
            'result_notes' => 'nullable|string|max:1000',
        ]);

        $installation->update($request->all());
        return redirect()->route('employee.installations.index')->with('success', 'Установка успешно обновлена');
    }

    public function confirm(Installation $installation, Request $request)
    {
        if (!$this->canConfirmInstallations()) {
            return redirect()->back()->with('error', 'У вас нет доступа к подтверждению установки');
        }

        // Проверяем последовательность конвейера: производство должно быть завершено
        $order = $installation->order;
        if (!$order->production || is_null($order->production->completed_at)) {
            return redirect()->back()->with('error', 'Невозможно подтвердить установку, пока производство не завершено для заказа №' . $order->order_number);
        }

        $request->validate([
            'result_notes' => 'nullable|string|max:1000',
        ]);

        $installation->update([
            'installed_at' => now(),
            'result_notes' => $request->result_notes
        ]);

        return redirect()->route('employee.installations.index')->with('success', 'Установка успешно подтверждена');
    }

    public function destroy(Installation $installation)
    {
        if (!$this->canManageInstallations()) {
            return redirect()->back()->with('error', 'У вас нет доступа к удалению установок');
        }

        $installation->delete();
        return redirect()->route('employee.installations.index')->with('success', 'Установка удалена');
    }
} 