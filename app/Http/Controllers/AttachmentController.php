<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Measurement;
use App\Components\SaveMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Traits\HasRolePermissions;

class AttachmentController extends Controller
{
    use HasRolePermissions;

    public array $media = [];

    public function attachMediaByOrder(Request $request, Order $order)
    {
        if (!$this->canManageOrders()) {
            return redirect()->back()->with('error', 'Вы не можете добавлять медиа файлы к заказам');
        }

        $request->validate([
            'media' => 'required|array',
            'media.*' => 'required|file|max:10240',
            'comment' => 'nullable|string',
        ]);

        $service = app(SaveMedia::class);

        $service->attachable_type = Order::class;
        $service->attachable_id = $order->id;

        $service->file = $request->file('media');
        $service->comment = $request->comment ?? '';
        $success = $service->save();

        if ($success) {
            Log::info('Медиа файлы добавлены к заказу', [
                'order_id' => $order->id,
                'user_id' => $this->getCurrentUser()->id
            ]);

            return redirect()->back()->with('success', 'Медиа файлы успешно добавлены');
        }

        return redirect()->back()->with('error', 'Не удалось добавить медиа файлы');
    }

    public function attachMediaByMeasurement(Request $request, Measurement $measurement)
    {
        if (!$measurement->order) {
            return redirect()->back()->with('error', 'Замер не связан с заказом');
        }

        if ($measurement->order->trashed()) {
            return redirect()->back()->with('error', 'Невозможно добавить медиа файлы, так как связанный заказ был удален.');
        }

        // Проверяем, может ли пользователь работать с замерами
        if (!$this->canManageMeasurements()) {
            return redirect()->back()->with('error', 'Вы не можете добавлять медиа файлы к замерам');
        }

        // Дополнительная проверка для замерщика - может работать только со своими замерами
        if ($this->isSurveyor() && $measurement->surveyor_id !== $this->getCurrentUser()->id) {
            return redirect()->back()->with('error', 'Вы можете добавлять медиа файлы только к своим замерам');
        }

        $request->validate([
            'media' => 'required|array',
            'media.*' => 'required|file|max:10240',
            'comment' => 'nullable|string',
        ]);

        $service = app(SaveMedia::class);
        $service->attachable_type = Measurement::class;
        $service->attachable_id = $measurement->id;
        $service->file = $request->file('media');
        $service->comment = $request->comment ?? '';
        $success = $service->save();
        
        return redirect()->back()->with('success', 'Медиа файлы успешно добавлены');
    }

    public function attachMediaByDocumentation(Request $request, \App\Models\Documentation $documentation)
    {
        if (!$documentation->order) {
            return redirect()->back()->with('error', 'Документация не связана с заказом');
        }

        if ($documentation->order->trashed()) {
            return redirect()->back()->with('error', 'Невозможно добавить медиа файлы, так как связанный заказ был удален.');
        }

        // Проверяем, может ли пользователь работать с документацией
        if (!$this->canManageDocumentation()) {
            return redirect()->back()->with('error', 'Вы не можете добавлять медиа файлы к документации');
        }

        // Дополнительная проверка для конструктора - может работать только со своей документацией  
        if ($this->isConstructor() && $documentation->constructor_id !== $this->getCurrentUser()->id) {
            return redirect()->back()->with('error', 'Вы можете добавлять медиа файлы только к своей документации');
        }

        $request->validate([
            'media' => 'required|array',
            'media.*' => 'required|file|max:10240',
            'comment' => 'nullable|string',
        ]);

        $service = app(SaveMedia::class);
        $service->attachable_type = \App\Models\Documentation::class;
        $service->attachable_id = $documentation->id;
        $service->file = $request->file('media');
        $service->comment = $request->comment ?? '';
        $success = $service->save();

        if ($success) {
            Log::info('Медиа файлы добавлены к документации', [
                'documentation_id' => $documentation->id,
                'user_id' => $this->getCurrentUser()->id,
                'order_id' => $documentation->order_id
            ]);

            return redirect()->back()->with('success', 'Медиа файлы успешно добавлены к документации');
        }

        return redirect()->back()->with('error', 'Не удалось добавить медиа файлы');
    }

    private function canManageDocumentation()
    {
        $role = $this->getCurrentUser()->role;
        return in_array($role, ['manager', 'constructor', 'installer']);
    }

    private function isConstructor()
    {
        return $this->getCurrentUser()->role === 'constructor';
    }
}
