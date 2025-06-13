<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Components\CalendarService;
use Illuminate\Support\Facades\View;
use App\Traits\HasRolePermissions;

class CalendarController extends Controller
{
    use HasRolePermissions;

    public function index(Request $request, CalendarService $calendarService)
    {
        if (!$this->canViewCalendar()) {
            return redirect()->route('employee.orders.index')->with('error', 'У вас нет доступа к календарю');
        }

        $modelClass = $request->get('model', \App\Models\Order::class);
        $dateField = $request->get('dateField', $modelClass === \App\Models\Order::class ? 'meeting_at' : 'measured_at');

        if (!class_exists($modelClass)) {
            abort(404, 'Модель не найдена');
        }

        $month = now()->month;
        $year = now()->year;

        $startOfMonth = now()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endOfMonth = now()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        // Фильтрация данных в зависимости от роли
        $query = $modelClass::whereNotNull($dateField)
            ->whereBetween($dateField, [$startOfMonth, $endOfMonth]);

        // Для замерщика показываем только его замеры
        if ($this->isSurveyor() && $modelClass === \App\Models\Measurement::class) {
            $query->where('surveyor_id', $this->getCurrentUser()->id);
        }

        // Для заказов показываем все, если пользователь может их просматривать
        if ($modelClass === \App\Models\Order::class && !$this->canViewOrders()) {
            return redirect()->route('employee.orders.index')->with('error', 'У вас нет доступа к заказам');
        }

        // Добавляем eager loading в зависимости от модели
        if ($modelClass === \App\Models\Order::class) {
            $query->with(['measurement', 'contract', 'documentation', 'installation', 'manager', 'surveyor', 'constructor', 'installer']);
        } elseif ($modelClass === \App\Models\Measurement::class) {
            $query->with(['order', 'surveyor']);
        }
        
        $records = $query->get();
        $grouped = $calendarService->groupByDate($records, $dateField);

        // Для таблицы - тоже применяем фильтры с eager loading
        $tableQuery = $modelClass::query();
        if ($this->isSurveyor() && $modelClass === \App\Models\Measurement::class) {
            $tableQuery->where('surveyor_id', $this->getCurrentUser()->id);
        }
        
        // Добавляем eager loading для таблицы
        if ($modelClass === \App\Models\Order::class) {
            $tableQuery->with(['measurement', 'contract', 'documentation', 'installation', 'manager', 'surveyor', 'constructor', 'installer']);
        } elseif ($modelClass === \App\Models\Measurement::class) {
            $tableQuery->with(['order', 'surveyor']);
        }
        
        $tableData = $tableQuery->get();

        if ($modelClass === \App\Models\Order::class) {
            return view('orders.index', [
                'orders' => $tableData,
                'grouped' => $grouped,
                'month' => $month,
                'year' => $year,
                'model' => $modelClass,
                'dateField' => $dateField,
            ]);
        } elseif ($modelClass === \App\Models\Measurement::class) {
            return view('measurements.index', [
                'measurements' => $tableData,
                'grouped' => $grouped,
                'month' => $month,
                'year' => $year,
                'model' => $modelClass,
                'dateField' => $dateField,
            ]);
        }
    }

    public function create()
    {
        if (!$this->canManageOrders()) {
            return redirect()->route('employee.orders.index')->with('error', 'У вас нет доступа к созданию заказов');
        }

        $preselectDate = request()->query('date');
        $preselectType = request()->query('type');

        return view('orders.create', compact('preselectDate', 'preselectType'));
    }

    public function partial(Request $request, CalendarService $calendarService)
    {
        if (!$this->canViewCalendar()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $model = $request->query('model', \App\Models\Order::class);
        $dateField = $request->query('dateField', $model === \App\Models\Order::class ? 'meeting_at' : 'measured_at');

        $month = intval($request->query('month', now()->month));
        $year = intval($request->query('year', now()->year));

        $startOfMonth = now()->setMonth($month)->setYear($year)->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfMonth = now()->setMonth($month)->setYear($year)->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $query = $model::whereNotNull($dateField)
            ->whereBetween($dateField, [$startOfMonth, $endOfMonth]);

        // Применяем фильтры в зависимости от роли
        if ($this->isSurveyor() && $model === \App\Models\Measurement::class) {
            $query->where('surveyor_id', $this->getCurrentUser()->id);
        }

        // Добавляем eager loading
        if ($model === \App\Models\Order::class) {
            $query->with(['measurement', 'contract', 'documentation', 'installation', 'manager', 'surveyor', 'constructor', 'installer']);
        } elseif ($model === \App\Models\Measurement::class) {
            $query->with(['order', 'surveyor']);
        }

        $records = $query->get();
        $grouped = $calendarService->groupByDate($records, $dateField);

        return view('calendar.partial', [
            'grouped' => $grouped,
            'month' => $month,
            'year' => $year,
            'model' => $model,
            'dateField' => $dateField,
        ]);
    }
}
