<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Measurement;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Components\SaveMedia;
use App\Components\CalendarService;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Traits\HasRolePermissions;

class MeasurementController extends Controller
{
    use HasRolePermissions;

    public function index(Request $request, CalendarService $calendarService)
    {
        $user = Auth::guard('employees')->user();
        $surveyors = User::where('role', 'surveyor')->get();
        $measurements = null;

        // Базовый запрос для всех ролей
        $measurementsQuery = Measurement::whereHas('order', function ($query) {
            $query->whereNull('deleted_at');
        })->withoutTrashed();

        if ($user->role === 'surveyor') {
            // Замерщик видит только свои замеры + незакрепленные
            $measurementsQuery->where(function ($query) use ($user) {
                $query->where('surveyor_id', $user->id)
                      ->orWhereNull('surveyor_id');
            });
        }
        elseif ($user->role === 'constructor') {
            // Конструктор видит только свои замеры + незакрепленные
            $measurementsQuery->where(function ($query) use ($user) {
                $query->where('measurements.surveyor_id', $user->id)
                      ->orWhereNull('measurements.surveyor_id');
            });
        }
         elseif ($user->role === 'manager') {
            // Менеджер видит все замеры, может фильтровать по конкретному замерщику
            if ($request->has('currentUserMeasurements')) {
                $measurementsQuery->where('surveyor_id', $request->input('currentUserMeasurements'));
            }
            // Если фильтрации нет, менеджер видит ВСЕ замеры (дополнительных условий не нужно)
        } else {
            // Другие роли (конструктор, установщик) видят все замеры, но могут фильтровать
            if ($request->has('currentUserMeasurements')) {
                $measurementsQuery->where('surveyor_id', $request->input('currentUserMeasurements'));
            }
        }

        $measurements = $measurementsQuery->with(['order.manager', 'surveyor'])->latest()->get();

        // Календарные данные
        $month = now()->month;
        $year = now()->year;
        $startOfMonth = now()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfMonth = now()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $records = Measurement::whereNotNull('measured_at')
            ->whereHas('order', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->whereBetween('measured_at', [$startOfMonth, $endOfMonth])
            ->get();
        $grouped = $calendarService->groupByDate($records, 'measured_at');

        // Получаем все вложения для замеров текущего surveyor через morphTo
        $attachments = \App\Models\Attachment::whereHasMorph(
            'attachable',
            [\App\Models\Measurement::class],
            function ($query) use ($user) {
                $query->where('surveyor_id', $user->id);
            }
        )->get();

        $employees = User::all(['id', 'name', 'role']);
        $selectedEmployee = $request->get('currentUserMeasurements') ? User::find($request->get('currentUserMeasurements')) : null;

        return view('dashboard.measurements.index', [
            'measurements' => $measurements,
            'grouped' => $grouped,
            'month' => $month,
            'year' => $year,
            'model' => \App\Models\Measurement::class,
            'dateField' => 'measured_at',
            'surveyors' => $surveyors,
            'attachments' => $attachments,
            'employees' => $employees,
            'selectedEmployee' => $selectedEmployee,
        ]);
    }

    public function create()
    {
        if (!$this->canManageMeasurements()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию замеров');
        }
        
        $orders = Order::where('status', '!=', 'completed')->get(['id', 'order_number', 'customer_name']);
        $surveyors = User::where('role', 'surveyor')->get(['id', 'name']);
        
        return view('dashboard.measurements.create', compact('orders', 'surveyors'));
    }

    public function store(Request $request)
    {
        if (!$this->canManageMeasurements()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию замеров');
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'surveyor_id' => 'required|exists:users,id',
            'measured_at' => 'nullable|date',
            'initial_meeting_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,completed',
        ]);

        $measurement = Measurement::create($request->all());

        Log::info('Создан новый замер', [
            'measurement_id' => $measurement->id,
            'user_id' => $this->getCurrentUser()->id
        ]);

        return redirect()->route('employee.measurements.index')->with('success', 'Замер успешно создан');
    }

    public function show(Measurement $measurement)
    {
        if (!$this->canManageMeasurements()) {
            return redirect()->back()->with('error', 'У вас нет доступа к просмотру замеров');
        }
        return view('dashboard.measurements.show', compact('measurement'));
    }

    public function edit(Measurement $measurement)
    {
        if (!$this->canManageMeasurements()) {
            return redirect()->back()->with('error', 'У вас нет доступа к редактированию замеров');
        }
        
        $orders = Order::all(['id', 'order_number', 'customer_name']);
        $surveyors = User::where('role', 'surveyor')->get(['id', 'name']);
        
        return view('dashboard.measurements.edit', compact('measurement', 'orders', 'surveyors'));
    }

    public function update(Request $request, Measurement $measurement)
    {
        if (!$this->canManageMeasurements()) {
            return redirect()->back()->with('error', 'У вас нет доступа к обновлению замеров');
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'surveyor_id' => 'required|exists:users,id',
            'measured_at' => 'nullable|date',
            'initial_meeting_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,completed',
        ]);

        $measurement->update($request->all());

        Log::info('Замер обновлен', [
            'measurement_id' => $measurement->id,
            'user_id' => $this->getCurrentUser()->id
        ]);

        return redirect()->route('employee.measurements.index')->with('success', 'Замер успешно обновлен');
    }

    public function addNote(Request $request, Measurement $measurement)
    {
        if (!$this->canManageMeasurements()) {
            return redirect()->back()->with('error', 'Вы не можете добавлять заметки');
        }
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $measurement->notes = $request->note;
        $measurement->save();

        Log::info('Добавлена заметка к замеру', [
            'measurement_id' => $measurement->id,
            'user_id' => $this->getCurrentUser()->id
        ]);

        return redirect()->back()->with('success', 'Заметка успешно добавлена');
    }

    public function timeChange(Request $request, Measurement $measurement)
    {
        if (!$this->canManageMeasurements()) {
            return redirect()->back()->with('error', 'Вы не можете назначить время измерения');
        }

        if (
            !$this->isManager() && // если не менеджер
            $measurement->surveyor_id !== null &&
            $measurement->surveyor_id !== $this->getCurrentUser()->id
        ) {
            return redirect()->back()->with('error', 'Этот замер закреплён за другим пользователем');
        }

        $request->validate([
            'time_change' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if(!$this->isManager() && $measurement->surveyor_id == null) {
            $measurement->surveyor_id = $this->getCurrentUser()->id;
        }
        
        $measurement->status = Measurement::STATUS_PENDING;

        if ($request->filled('notes')) {
            $measurement->notes = $request->input('notes');
        }

        $measurement->measured_at = $request->input('time_change');
        $measurement->save();

        Log::info('Время замера изменено', [
            'measurement_id' => $measurement->id,
            'user_id' => $this->getCurrentUser()->id,
            'new_time' => $measurement->measured_at
        ]);

        return redirect()->back()->with('success', 'Время измерения успешно изменено');
    }

    public function destroy(string $id)
    {
        if (!$this->canManageMeasurements()) {
            return redirect()->back()->with('error', 'У вас нет доступа к удалению замеров');
        }
        $measurement = Measurement::findOrFail($id);
        $measurement->delete();
        return redirect()->back()->with('success', 'Замер успешно удален');
    }

    public function restore(string $id)
    {
        if (!$this->canManageMeasurements()) {
            return redirect()->back()->with('error', 'У вас нет доступа к восстановлению замеров');
        }
        $measurement = Measurement::onlyTrashed()->findOrFail($id);
        $measurement->restore();
        return redirect()->back()->with('success', 'Замер успешно восстановлен');
    }


    public function complete(Request $request, Measurement $measurement)
    {
        if (!$this->canManageMeasurements() || ($this->isSurveyor() && $this->getCurrentUser()->id !== $measurement->surveyor_id)) {
            return redirect()->back()->with('error', 'Вы не можете отметить замер как сданный');
        }

        if(!$measurement->measured_at) {
            return redirect()->back()->withErrors('Замер не измерен');
        }

        if (!$measurement->order) {
            return redirect()->back()->with('error', 'Замер не связан с заказом');
        }

        // Валидация поля notes
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        // Если конструктор не назначен и текущий пользователь не менеджер, назначаем текущего пользователя
        if (!$measurement->order->constructor_id && !$this->isManager()) {
            $measurement->order->update(['constructor_id' => $this->getCurrentUser()->id]);
        }
        
        $measurement->status = Measurement::STATUS_COMPLETED;
        $measurement->uploaded = now();
        
        // Обновляем поле notes, принимая разные названия полей формы
        if ($request->filled('notes')) {
            $measurement->notes = $request->input('notes');
        } elseif ($request->filled('note')) {
            $measurement->notes = $request->input('note');
        }
        
        $measurement->save();

        Log::info('Замер отмечен как сданный', [
            'measurement_id' => $measurement->id,
            'user_id' => $this->getCurrentUser()->id,
            'order_id' => $measurement->order_id,
            'notes' => $measurement->notes
        ]);

        return redirect()->back()->with('success', 'Замер успешно отмечен как сданный');
    }
}
