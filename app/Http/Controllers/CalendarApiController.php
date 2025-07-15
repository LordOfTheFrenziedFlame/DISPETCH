<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Measurement;
use App\Models\Order;
use App\Models\Documentation;
use App\Models\Production;
use App\Models\Installation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasRolePermissions;
use Carbon\Carbon;

class CalendarApiController extends Controller
{
    use HasRolePermissions;

    public function index(Request $request)
    {
        // Общая проверка доступа к календарным данным
        if (!$this->canViewCalendar()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $type = $request->query('type');
        $employeeId = $request->query('employee_id'); // Универсальный параметр для фильтрации
        $managerId = $request->query('manager_id'); // Обратная совместимость
        $currentUserMeasurements = $request->query('currentUserMeasurements'); // Обратная совместимость
        $user = $this->getCurrentUser();

        // Нормализуем параметры фильтрации
        $filterId = $employeeId ?? $managerId ?? $currentUserMeasurements;

        $orderEvents         = collect();
        $measurementEvents   = collect();
        $contractEvents      = collect();
        $documentationEvents = collect();
        $productionEvents    = collect();
        $installationEvents  = collect();

        // --- Helper for unified event formatting ---
        $formatEvent = function ($item, $eventType, $title, $dateField = null, $url = null) use ($request) {
            $date = null;
            $backgroundColor = '#2196f3'; // Базовый синий цвет
            $textColor = '#ffffff';
            
            // Определяем дату события
            switch($eventType) {
                case 'order':
                    $date = $item->meeting_at;
                    $backgroundColor = $this->getOrderColor($item);
                    break;
                case 'measurement':
                    $date = $item->measured_at;
                    $backgroundColor = $this->getMeasurementColor($item);
                    break;
                case 'contract':
                    $date = $item->signed_at ?? $item->documentation_due_at;
                    $backgroundColor = $this->getContractColor($item);
                    break;
                case 'documentation':
                    $date = optional($item->order->contract)->documentation_due_at;
                    $backgroundColor = $this->getDocumentationColor($item);
                    break;
                case 'production':
                    $date = optional($item->order->contract)->ready_date;
                    $backgroundColor = $this->getProductionColor($item);
                    break;
                case 'installation':
                    $date = optional($item->order->contract)->installation_date;
                    $backgroundColor = $this->getInstallationColor($item);
                    break;
            }

            return [
                'type' => $eventType,
                'id' => $item->id,
                'title' => $title,
                'start' => $date ? Carbon::parse($date)->toDateString() : null,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'textColor' => $textColor,
                'url' => $url,
                'extendedProps' => [
                    'orderId' => $eventType === 'order' ? $item->id : ($item->order->id ?? null),
                    'contractNumber' => $eventType === 'order' ? optional($item->contract)->contract_number : ($eventType === 'contract' ? $item->contract_number : optional($item->order->contract)->contract_number),
                    'customerName' => $eventType === 'order' ? $item->customer_name : ($item->order->customer_name ?? null),
                    'status' => $item->status ?? null,
                    'eventType' => $eventType,
                ]
            ];
        };

        // Заказы
        if (!$type || $type === 'order') {
            $ordersQuery = Order::whereNotNull('meeting_at')
                ->whereNull('deleted_at');
            
            $this->applyOrderFilters($ordersQuery, $filterId);

            $orderEvents = $ordersQuery->with(['manager', 'surveyor', 'constructor', 'installer', 'contract'])->get()->map(function ($item) use ($formatEvent) {
                $title = sprintf(
                    '#%s - %s - %s',
                    $item->id,
                    optional($item->contract)->contract_number ?? 'Б/Н',
                    $item->customer_name
                );
                
                return $formatEvent($item, 'order', $title, 'meeting_at');
            });
        }

        // Замеры
        if (!$type || $type === 'measurement') {
            $measurementsQuery = Measurement::whereNotNull('measured_at');

            $this->applyMeasurementFilters($measurementsQuery, $filterId);

            $measurementEvents = $measurementsQuery->with(['order.contract', 'surveyor'])->get()->map(function ($item) use ($formatEvent) {
                $title = sprintf(
                    '#%d - %s - %s',
                    $item->id,
                    optional($item->order->contract)->contract_number ?? 'Б/Н',
                    optional($item->order)->customer_name ?? 'Неизвестен'
                );
                
                return $formatEvent($item, 'measurement', $title, 'measured_at');
            });
        }

        // Договоры
        if ($type === 'contract') {
            if (!$this->canViewContracts()) {
                return response()->json(['error' => 'Access denied to contracts'], 403);
            }

            $contractsQuery = \App\Models\Contract::with('order');
            $this->applyContractFilters($contractsQuery, $filterId);

            $contractEvents = $contractsQuery->get()->map(function ($contract) use ($formatEvent) {
                $title = sprintf(
                    '#%d - %s - %s',
                    $contract->id,
                    $contract->contract_number ?? 'Б/Н',
                    optional($contract->order)->customer_name ?? 'Неизвестен'
                );
                
                return $formatEvent($contract, 'contract', $title);
            });
        }

        // Документации
        if (!$type || $type === 'documentation') {
            $docsQuery = Documentation::with('order.contract');
            $this->applyDocumentationFilters($docsQuery, $filterId);

            $documentationEvents = $docsQuery->get()->map(function ($d) use ($formatEvent) {
                $title = sprintf(
                    '#%d - %s - %s',
                    $d->id,
                    optional($d->order->contract)->contract_number ?? 'Б/Н',
                    optional($d->order)->customer_name ?? 'Неизвестен'
                );
                
                return $formatEvent($d, 'documentation', $title);
            });
        }

        // Производства
        if (!$type || $type === 'production') {
            $prodQuery = Production::with('order.contract');
            $this->applyProductionFilters($prodQuery, $filterId);

            $productionEvents = $prodQuery->get()->map(function ($p) use ($formatEvent) {
                $title = sprintf(
                    '#%d - %s - %s',
                    $p->id,
                    optional($p->order->contract)->contract_number ?? 'Б/Н',
                    optional($p->order)->customer_name ?? 'Неизвестен'
                );
                
                return $formatEvent($p, 'production', $title);
            });
        }

        // Установки
        if (!$type || $type === 'installation') {
            $instQuery = Installation::with('order.contract');
            $this->applyInstallationFilters($instQuery, $filterId);

            $installationEvents = $instQuery->get()->map(function ($i) use ($formatEvent) {
                $title = sprintf(
                    '#%d - %s - %s',
                    $i->id,
                    optional($i->order->contract)->contract_number ?? 'Б/Н',
                    optional($i->order)->customer_name ?? 'Неизвестен'
                );
                
                return $formatEvent($i, 'installation', $title);
            });
        }

        $events = collect()
            ->merge($orderEvents)
            ->merge($measurementEvents)
            ->merge($contractEvents)
            ->merge($documentationEvents)
            ->merge($productionEvents)
            ->merge($installationEvents)
            ->values();

        return response()->json($events);
    }

    /**
     * Применить фильтры для заказов
     */
    private function applyOrderFilters($query, $filterId)
    {
        // Фильтрация по ролям
        if ($this->isSurveyor()) {
            // Замерщик видит только свои заказы или без назначенного замерщика
            $query->where(function($q) {
                $q->where('surveyor_id', $this->getCurrentUser()->id)
                  ->orWhereNull('surveyor_id');
            });
        } elseif ($this->isConstructor()) {
            // Конструктор видит заказы в работе
            $query->whereIn('status', ['in_progress', 'completed']);
        } elseif ($this->isInstaller()) {
            // Установщик видит заказы с готовой документацией
            $query->whereHas('documentation', function($q) {
                $q->whereNotNull('completed_at');
            });
        } elseif ($this->isManager() && $filterId) {
            // Менеджер может фильтровать по ID менеджера
            $query->where('manager_id', $filterId);
        }
    }

    /**
     * Применить фильтры для замеров
     */
    private function applyMeasurementFilters($query, $filterId)
    {
        $user = $this->getCurrentUser();
        
        // Фильтрация по не удалённым заказам
        $query->whereHas('order', function ($q) {
            $q->whereNull('deleted_at');
        });
        
        // Фильтрация по ролям для замеров
        if ($this->isSurveyor()) {
            // Замерщик видит только свои замеры или незакрепленные
            $query->where(function ($q) use ($user) {
                $q->where('surveyor_id', $user->id)
                  ->orWhereNull('surveyor_id');
            });
        } elseif ($this->isConstructor()) {
            // Конструктор видит только свои замеры или незакрепленные
            $query->where(function ($q) use ($user) {
                $q->where('surveyor_id', $user->id)
                  ->orWhereNull('surveyor_id');
            });
        } elseif ($this->isManager()) {
            // Менеджер видит все замеры, может фильтровать по конкретному замерщику
            if ($filterId) {
                $query->where('surveyor_id', $filterId);
            }
        } else {
            // Другие роли могут фильтровать
            if ($filterId) {
                $query->where('surveyor_id', $filterId);
            }
        }
    }

    /**
     * Применить фильтры для договоров
     */
    private function applyContractFilters($query, $filterId)
    {
        $query->whereHas('order', function($q) use ($filterId) {
            $q->whereNull('deleted_at');
            if ($this->isManager() && $filterId) {
                $q->where('manager_id', $filterId);
            }
        });
    }

    /**
     * Применить фильтры для документации
     */
    private function applyDocumentationFilters($query, $filterId)
    {
        $user = $this->getCurrentUser();
        
        $query->whereHas('order', function($q) {
            $q->whereNull('deleted_at');
        });

        // Фильтрация по ролям
        if ($this->isManager()) {
            // Менеджер видит всю документацию, может фильтровать по конкретному сотруднику
            if ($filterId) {
                $query->whereHas('order', function ($q) use ($filterId) {
                    $q->where(function ($subQ) use ($filterId) {
                        $subQ->where('manager_id', $filterId)
                             ->orWhere('surveyor_id', $filterId)
                             ->orWhere('constructor_id', $filterId)
                             ->orWhere('installer_id', $filterId);
                    });
                })->orWhere('constructor_id', $filterId);
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
        }
    }

    /**
     * Применить фильтры для производства
     */
    private function applyProductionFilters($query, $filterId)
    {
        $user = $this->getCurrentUser();
        
        $query->whereHas('order', function($q) {
            $q->whereNull('deleted_at');
        });

        // Фильтрация по ролям
        if ($this->isManager()) {
            // Менеджер видит всё производство, может фильтровать по конкретному сотруднику
            if ($filterId) {
                $query->whereHas('order', function ($q) use ($filterId) {
                    $q->where(function ($subQ) use ($filterId) {
                        $subQ->where('manager_id', $filterId)
                             ->orWhere('surveyor_id', $filterId)
                             ->orWhere('constructor_id', $filterId)
                             ->orWhere('installer_id', $filterId);
                    });
                });
            }
        } elseif ($this->isSurveyor()) {
            // Замерщик видит только производство по своим заказам
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('surveyor_id', $user->id);
            });
        } elseif ($this->isConstructor()) {
            // Конструктор видит производство по заказам, где он назначен конструктором
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('constructor_id', $user->id);
            });
        } elseif ($this->isInstaller()) {
            // Установщик видит производство по заказам, где он назначен установщиком
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('installer_id', $user->id);
            });
        }
    }

    /**
     * Применить фильтры для установок
     */
    private function applyInstallationFilters($query, $filterId)
    {
        $user = $this->getCurrentUser();
        
        $query->whereHas('order', function($q) {
            $q->whereNull('deleted_at');
        });

        // Фильтрация по ролям
        if ($this->isManager()) {
            // Менеджер видит все установки, может фильтровать по конкретному сотруднику
            if ($filterId) {
                $query->whereHas('order', function ($q) use ($filterId) {
                    $q->where(function ($subQ) use ($filterId) {
                        $subQ->where('manager_id', $filterId)
                             ->orWhere('surveyor_id', $filterId)
                             ->orWhere('constructor_id', $filterId)
                             ->orWhere('installer_id', $filterId);
                    });
                });
            }
        } elseif ($this->isSurveyor()) {
            // Замерщик видит только установки по своим заказам
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('surveyor_id', $user->id);
            });
        } elseif ($this->isConstructor()) {
            // Конструктор видит установки по заказам, где он назначен конструктором
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('constructor_id', $user->id);
            });
        } elseif ($this->isInstaller()) {
            // Установщик видит установки, где он назначен установщиком
            $query->whereHas('order', function ($q) use ($user) {
                $q->where('installer_id', $user->id);
            });
        }
    }

    /**
     * Получить цвет для заказа
     */
    private function getOrderColor($order)
    {
        $isActiveOrDone = in_array($order->status, ['in_progress', 'completed']);
        
        if ($isActiveOrDone) {
            return '#4caf50'; // зелёный – выполнено или в работе
        }

        $deadline = $order->meeting_at ? Carbon::parse($order->meeting_at) : null;
        return $this->getColorByDeadline($deadline);
    }

    /**
     * Получить цвет для замера
     */
    private function getMeasurementColor($measurement)
    {
        $isCompleted = $measurement->isCompleted();
        
        if ($isCompleted) {
            return '#4caf50'; // зелёный – выполнено
        }

        $deadline = $measurement->measured_at ? Carbon::parse($measurement->measured_at) : null;
        return $this->getColorByDeadline($deadline);
    }

    /**
     * Получить цвет для договора
     */
    private function getContractColor($contract)
    {
        if ($contract->signed_at) {
            return '#4caf50'; // зелёный – подписан
        }

        $deadline = $contract->documentation_due_at ? Carbon::parse($contract->documentation_due_at) : null;
        return $this->getColorByDeadline($deadline);
    }

    /**
     * Получить цвет для документации
     */
    private function getDocumentationColor($documentation)
    {
        if ($documentation->completed_at) {
            return '#4caf50'; // зелёный – завершена
        }

        $deadline = optional($documentation->order->contract)->documentation_due_at;
        $deadline = $deadline ? Carbon::parse($deadline) : null;
        return $this->getColorByDeadline($deadline);
    }

    /**
     * Получить цвет для производства
     */
    private function getProductionColor($production)
    {
        if ($production->completed_at) {
            return '#4caf50'; // зелёный – завершено
        }

        $deadline = optional($production->order->contract)->ready_date;
        $deadline = $deadline ? Carbon::parse($deadline) : null;
        return $this->getColorByDeadline($deadline);
    }

    /**
     * Получить цвет для установки
     */
    private function getInstallationColor($installation)
    {
        if ($installation->installed_at) {
            return '#4caf50'; // зелёный – установлена
        }

        $deadline = optional($installation->order->contract)->installation_date;
        $deadline = $deadline ? Carbon::parse($deadline) : null;
        return $this->getColorByDeadline($deadline);
    }

    /**
     * Получить цвет по дедлайну
     */
    private function getColorByDeadline(?Carbon $deadline)
    {
        if (is_null($deadline)) {
            return '#2196f3'; // синий – нет дедлайна
        }
        
        $today = Carbon::today();
        if ($deadline->lt($today)) {
            return '#e53935'; // красный – просрочено
        }
        if ($deadline->lte($today->copy()->addDays(3))) {
            return '#ffb300'; // жёлтый – крайний срок сегодня/завтра
        }
        return '#2196f3'; // синий – всё ещё в срок
    }

    /**
     * Получить список сотрудников для фильтрации
     */
    public function getEmployees(Request $request)
    {
        if (!$this->canViewCalendar()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $type = $request->query('type', 'all');
        $currentUser = $this->getCurrentUser();

        $employees = collect();

        switch ($type) {
            case 'managers':
                $employees = User::where('role', 'manager')->get();
                break;
            
            case 'surveyors':
                $employees = User::where('role', 'surveyor')->get();
                break;
                
            case 'constructors':
                $employees = User::where('role', 'constructor')->get();
                break;
                
            case 'installers':
                $employees = User::where('role', 'installer')->get();
                break;
                
            default:
                // Все сотрудники, доступные для текущего пользователя
                if ($this->isManager()) {
                    $employees = User::all();
                } else {
                    $employees = User::where('role', $currentUser->role)->get();
                }
                break;
        }

        return response()->json($employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'role' => $employee->role,
                'role_display' => $this->getRoleDisplay($employee->role),
            ];
        }));
    }

    /**
     * Получить отображаемое название роли
     */
    private function getRoleDisplay($role)
    {
        $roles = [
            'manager' => 'Менеджер',
            'surveyor' => 'Замерщик',
            'constructor' => 'Конструктор',
            'installer' => 'Монтажник',
        ];

        return $roles[$role] ?? $role;
    }
}
