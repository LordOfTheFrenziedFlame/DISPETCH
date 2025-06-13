<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Documentation;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasRolePermissions;

class DocumentationController extends Controller
{
    use HasRolePermissions;

    private function canViewDocumentation()
    {
        $role = Auth::guard('employees')->user()->role;
        return in_array($role, ['manager', 'constructor', 'installer', 'surveyor']);
    }

    private function canManageDocumentation()
    {
        $role = Auth::guard('employees')->user()->role;
        return in_array($role, ['manager', 'constructor', 'installer']);
    }

    private function canConfirmDocumentation()
    {
        $role = Auth::guard('employees')->user()->role;
        return in_array($role, ['manager', 'surveyor', 'constructor', 'installer']);
    }

    public function index(Request $request)
    {
        if (!$this->canViewDocumentation()) {
            return redirect()->route('employee.orders.index');
        }

        $user = $this->getCurrentUser();
        
        // Базовый запрос с фильтрацией неудаленных заказов
        $query = Documentation::with('order', 'constructor')
            ->whereHas('order', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->withoutTrashed();
        
        // Фильтрация документации в зависимости от роли
        if ($this->isManager()) {
            // Менеджер видит всю документацию, может фильтровать по конкретному сотруднику
            if ($request->has('manager_id')) {
                $employeeId = $request->input('manager_id');
                $query->whereHas('order', function ($q) use ($employeeId) {
                    $q->where(function ($subQ) use ($employeeId) {
                        $subQ->where('manager_id', $employeeId)
                             ->orWhere('surveyor_id', $employeeId)
                             ->orWhere('constructor_id', $employeeId)
                             ->orWhere('installer_id', $employeeId);
                    });
                })->orWhere('constructor_id', $employeeId);
            }
        } elseif ($this->isSurveyor()) {
            // Замерщик видит только документацию по своим заказам
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('surveyor_id', $user->id);
            });
        } elseif ($this->isConstructor()) {
            // Конструктор видит документацию, где он назначен конструктором
            $query->where('constructor_id', $user->id);
        } elseif ($this->isInstaller()) {
            // Установщик видит документацию по заказам, где он назначен установщиком
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('installer_id', $user->id);
            });
        } else {
            $documentations = collect();
            return view('dashboard.documentation.index', compact('documentations'));
        }

        $documentations = $query->latest()->get();
        return view('dashboard.documentation.index', compact('documentations'));
    }

    public function create()
    {
        if (!$this->canManageDocumentation()) {
            return redirect()->route('employee.orders.index');
        }

        return view('dashboard.documentation.create');
    }

    public function show(Documentation $documentation)
    {
        if (!$this->canViewDocumentation()) {
            return redirect()->back()->with('error', 'У вас нет доступа к просмотру документации');
        }

        $user = $this->getCurrentUser();
        
        // Дополнительная проверка доступа для ограниченных ролей
        if ($this->isSurveyor()) {
            // Замерщик может видеть только документацию по своим заказам
            if ($documentation->order->surveyor_id !== $user->id) {
                return redirect()->back()->with('error', 'У вас нет доступа к этой документации');
            }
        } elseif ($this->isConstructor()) {
            // Конструктор может видеть только свою документацию
            if ($documentation->constructor_id !== $user->id) {
                return redirect()->back()->with('error', 'У вас нет доступа к этой документации');
            }
        } elseif ($this->isInstaller()) {
            // Установщик может видеть только документацию по своим заказам
            if ($documentation->order->installer_id !== $user->id) {
                return redirect()->back()->with('error', 'У вас нет доступа к этой документации');
            }
        }

        return view('dashboard.documentation.show', compact('documentation'));
    }

    public function edit(Documentation $documentation)
    {
        if (!$this->canManageDocumentation()) {
            return redirect()->back()->with('error', 'У вас нет доступа к редактированию документации');
        }

        $user = $this->getCurrentUser();
        
        // Дополнительная проверка доступа для ограниченных ролей
        if ($this->isConstructor() && $documentation->constructor_id !== $user->id) {
            return redirect()->back()->with('error', 'Вы можете редактировать только свою документацию');
        } elseif ($this->isInstaller() && $documentation->order->installer_id !== $user->id) {
            return redirect()->back()->with('error', 'Вы можете редактировать только документацию по своим заказам');
        }

        return view('dashboard.documentation.edit', compact('documentation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'constructor_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'completed_at' => 'required|date',
        ]);

        if (!$this->canManageDocumentation()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию документации');
        }

        Documentation::create($validated);
        return redirect()->route('employee.documentations.index')->with('success', 'Документация успешно создана');
    }

    public function update(Request $request, Documentation $documentation)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'constructor_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
            'completed_at' => 'nullable|date',
        ]);

        if (!$this->canManageDocumentation()) {
            return redirect()->back()->with('error', 'У вас нет доступа к обновлению документации');
        }

        $user = $this->getCurrentUser();
        
        // Дополнительная проверка доступа для ограниченных ролей
        if ($this->isConstructor() && $documentation->constructor_id !== $user->id) {
            return redirect()->back()->with('error', 'Вы можете обновлять только свою документацию');
        } elseif ($this->isInstaller() && $documentation->order->installer_id !== $user->id) {
            return redirect()->back()->with('error', 'Вы можете обновлять только документацию по своим заказам');
        }

        $documentation->update($validated);
        return redirect()->route('employee.documentations.index')->with('success', 'Документация успешно обновлена');
    }

    public function confirm(Documentation $documentation, Request $request)
    {
        if (!$this->canConfirmDocumentation()) {
            return redirect()->back()->with('error', 'У вас нет доступа к подтверждению документации');
        }

        // Если установщик не назначен и текущий пользователь не менеджер, назначаем текущего пользователя
        if (!$documentation->order->installer_id && !$this->isManager()) {
            $documentation->order->update(['installer_id' => $this->getCurrentUser()->id]);
        }

        $documentation->update(['completed_at' => now(), 'status' => 'completed']);
        return redirect()->route('employee.documentations.index')->with('success', 'Документация успешно подтверждена');
    }

    public function destroy(Documentation $documentation)
    {
        if (!$this->canManageDocumentation()) {
            return redirect()->back()->with('error', 'У вас нет доступа к удалению документации');
        }

        $user = $this->getCurrentUser();
        
        // Дополнительная проверка доступа для ограниченных ролей
        if ($this->isConstructor() && $documentation->constructor_id !== $user->id) {
            return redirect()->back()->with('error', 'Вы можете удалять только свою документацию');
        } elseif ($this->isInstaller() && $documentation->order->installer_id !== $user->id) {
            return redirect()->back()->with('error', 'Вы можете удалять только документацию по своим заказам');
        }

        $documentation->delete();
        return redirect()->route('employee.documentations.index')->with('success', 'Документация удалена');
    }
}
