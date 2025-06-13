<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Measurement;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasRolePermissions;

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

        $orderEvents = collect();
        $measurementEvents = collect();
        $contractEvents = collect();

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

            $orderEvents = $ordersQuery->with(['manager', 'surveyor', 'constructor', 'installer'])->get()->map(function ($item) {
                return [
                    'type' => 'order',
                    'id' => $item->id,
                    'title' => 'Заказ №' . $item->id . ' - ' . $item->customer_name,
                    'start' => optional($item->meeting_at)->toDateString(),
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

            $measurementEvents = $measurementsQuery->with('order')->get()->map(function ($item) {
                return [
                    'type' => 'measurement',
                    'id' => $item->id,
                    'title' => optional($item->order)->customer_name ?? 'Замер №' . $item->id,
                    'start' => $item->measured_at ? \Carbon\Carbon::parse($item->measured_at)->toIso8601String() : null,
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

            $contractEvents = $contractsQuery->get()->map(function ($contract) {
                return [
                    'type' => 'contract',
                    'id' => $contract->id,
                    'title' => 'Договор №' . $contract->contract_number . ' (' . optional($contract->order)->customer_name . ')',
                    'start' => $contract->signed_at ? \Carbon\Carbon::parse($contract->signed_at)->toIso8601String() : null,
                    'url' => route('employee.contracts.index') . '#contract-' . $contract->id,
                    'backgroundColor' => '#4caf50',
                    'borderColor' => '#388e3c',
                ];
            });
        }

        $events = $orderEvents->merge($measurementEvents)->merge($contractEvents)->values();

        return response()->json($events);
    }
}
