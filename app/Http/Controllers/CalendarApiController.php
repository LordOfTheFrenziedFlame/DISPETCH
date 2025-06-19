<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Measurement;
use App\Models\Order;
use App\Models\Documentation;
use App\Models\Production;
use App\Models\Installation;
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
        $currentUserMeasurements = $request->query('currentUserMeasurements');
        $managerId = $request->query('manager_id');
        $user = $this->getCurrentUser();

        $orderEvents         = collect();
        $measurementEvents   = collect();
        $contractEvents      = collect();
        $documentationEvents = collect();
        $productionEvents    = collect();
        $installationEvents  = collect();

        // --- Helper for color selection ---
        $colorFor = function (?Carbon $deadline, $done = null) {
            // Первым делом проверяем факт завершения / активной работы
            // (done может быть boolean или Carbon – нам важна лишь истинность)
            if ($done) {
                return '#4caf50'; // зелёный – выполнено или в работе
            }

            // Далее обычная логика дедлайнов
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
        };

        // Заказы
        if (!$type || $type === 'order') {
            $ordersQuery = Order::whereNotNull('meeting_at')
                ->whereNull('deleted_at'); // Исключаем удаленные заказы
            
            // Фильтрация по ролям
            if ($this->isSurveyor()) {
                // Замерщик видит только свои заказы или без назначенного замерщика
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
                // Менеджер может фильтровать по ID менеджера
                $ordersQuery->where('manager_id', $managerId);
            }

            $orderEvents = $ordersQuery->with(['manager', 'surveyor', 'constructor', 'installer', 'contract'])->get()->map(function ($item) use ($colorFor) {
                $isActiveOrDone = in_array($item->status, ['in_progress', 'completed']);

                return [
                    'type' => 'order',
                    'id' => $item->id,
                    'title' => 'Заказ #' . $item->id . ' (Заказ #' . $item->order_number . ' - ' . $item->customer_name . ')',
                    'start' => optional($item->meeting_at)->toDateString(),
                    'backgroundColor' => $colorFor($item->meeting_at ? Carbon::parse($item->meeting_at) : null, $isActiveOrDone),
                    'borderColor'     => $colorFor($item->meeting_at ? Carbon::parse($item->meeting_at) : null, $isActiveOrDone),
                ];
            });
        }

        // Замеры
        if (!$type || $type === 'measurement') {
            $measurementsQuery = Measurement::whereNotNull('measured_at');

            // Фильтрация по ролям для замеров
            if ($this->isSurveyor()) {
                // Замерщик видит только свои замеры
                $measurementsQuery->where('surveyor_id', $user->id);
            } elseif ($this->isManager() && $currentUserMeasurements) {
                // Менеджер может фильтровать по конкретному замерщику
                $measurementsQuery->where('surveyor_id', $currentUserMeasurements);
            } elseif (!$this->isManager()) {
                // Конструктор и установщик видят замеры в общем доступе
                // (могут быть ограничены дополнительно если нужно)
            }

            // Фильтрация по не удалённым заказам
            $measurementsQuery->whereHas('order', function ($query) {
                $query->whereNull('deleted_at');
            });

            $measurementEvents = $measurementsQuery->with('order')->get()->map(function ($item) use ($colorFor) {
                $isCompleted = $item->isCompleted();

                return [
                    'type' => 'measurement',
                    'id' => $item->id,
                    'title' => 'Замер #' . $item->id . ' (Заказ #' . optional($item->order)->order_number . ' - ' . optional($item->order)->customer_name . ')',
                    'start' => $item->measured_at ? Carbon::parse($item->measured_at)->toIso8601String() : null,
                    'backgroundColor' => $colorFor($item->measured_at ? Carbon::parse($item->measured_at) : null, $isCompleted),
                    'borderColor'     => $colorFor($item->measured_at ? Carbon::parse($item->measured_at) : null, $isCompleted),
                ];
            });
        }

        // Договоры
        if ($type === 'contract') {
            // Только пользователи с доступом к договорам могут их видеть
            if (!$this->canViewContracts()) {
                return response()->json(['error' => 'Access denied to contracts'], 403);
            }

            $contractsQuery = \App\Models\Contract::whereNotNull('signed_at')->with('order');
            $contractsQuery->whereHas('order', function($q) use ($managerId) {
                $q->whereNull('deleted_at');
                if ($this->isManager() && $managerId) {
                    $q->where('manager_id', $managerId);
                }
            });

            $contractEvents = $contractsQuery->get()->map(function ($contract) use ($colorFor) {
                return [
                    'type' => 'contract',
                    'id' => $contract->id,
                    'title' => 'Договор #' . $contract->id . ' (Заказ #' . optional($contract->order)->order_number . ' - ' . optional($contract->order)->customer_name . ')',
                    'start' => $contract->signed_at ? \Carbon\Carbon::parse($contract->signed_at)->toIso8601String() : null,
                    'url' => route('employee.contracts.index') . '#contract-' . $contract->id,
                    'backgroundColor' => $colorFor($contract->documentation_due_at, $contract->signed_at),
                    'borderColor'     => $colorFor($contract->documentation_due_at, $contract->signed_at),
                ];
            });
        }

        // Документации
        if (!$type || $type === 'documentation') {
            $docsQuery = Documentation::with('order.contract');

            $docsQuery->whereHas('order', function($q){
                $q->whereNull('deleted_at');
            });

            $documentationEvents = $docsQuery->get()->map(function ($d) use ($colorFor) {
                $deadline = optional(optional($d->order)->contract)->documentation_due_at;
                return [
                    'type'  => 'documentation',
                    'id'    => $d->id,
                    'title' => 'Документация #' . $d->id . ' (Заказ #' . optional($d->order)->order_number . ' - ' . optional($d->order)->customer_name . ')',
                    'start' => $deadline ? Carbon::parse($deadline)->toIso8601String() : null,
                    'backgroundColor' => $colorFor($deadline ? Carbon::parse($deadline) : null, $d->completed_at ? Carbon::parse($d->completed_at) : null),
                    'borderColor'     => $colorFor($deadline ? Carbon::parse($deadline) : null, $d->completed_at ? Carbon::parse($d->completed_at) : null),
                ];
            });
        }

        // Производства
        if (!$type || $type === 'production') {
            $prodQuery = Production::with('order.contract');
            $prodQuery->whereHas('order', function($q){
                $q->whereNull('deleted_at');
            });

            $productionEvents = $prodQuery->get()->map(function ($p) use ($colorFor) {
                $deadline = optional(optional($p->order)->contract)->ready_date;
                return [
                    'type'  => 'production',
                    'id'    => $p->id,
                    'title' => 'Производство #' . $p->id . ' (Заказ #' . optional($p->order)->order_number . ' - ' . optional($p->order)->customer_name . ')',
                    'start' => $deadline ? Carbon::parse($deadline)->toIso8601String() : null,
                    'backgroundColor' => $colorFor($deadline ? Carbon::parse($deadline) : null, $p->completed_at),
                    'borderColor'     => $colorFor($deadline ? Carbon::parse($deadline) : null, $p->completed_at),
                ];
            });
        }

        // Установки
        if (!$type || $type === 'installation') {
            $instQuery = Installation::with('order.contract');
            $instQuery->whereHas('order', function($q){
                $q->whereNull('deleted_at');
            });

            $installationEvents = $instQuery->get()->map(function ($i) use ($colorFor) {
                $deadline = optional(optional($i->order)->contract)->installation_date;
                return [
                    'type'  => 'installation',
                    'id'    => $i->id,
                    'title' => 'Установка #' . $i->id . ' (Заказ #' . optional($i->order)->order_number . ' - ' . optional($i->order)->customer_name . ')',
                    'start' => $deadline ? Carbon::parse($deadline)->toIso8601String() : null,
                    'backgroundColor' => $colorFor($deadline ? Carbon::parse($deadline) : null, $i->installed_at),
                    'borderColor'     => $colorFor($deadline ? Carbon::parse($deadline) : null, $i->installed_at),
                ];
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
}
