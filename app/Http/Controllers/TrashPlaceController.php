<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Measurement;
use App\Models\Attachment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasRolePermissions;

class TrashPlaceController extends Controller
{
    use HasRolePermissions;

    public function index()
    {
        if (!$this->canManageTrash()) {
            return redirect()->route('employee.orders.index')->with('error', 'У вас нет доступа к корзине');
        }

        $orders = Order::onlyTrashed()
            ->with([
                'measurement' => function ($q) {
                    $q->withTrashed();
                },
                'attachments'
            ])
            ->get();

        return view('orders-archived', compact('orders'));
    }

    public function restore(string $id)
    {
        if (!$this->canManageTrash()) {
            return redirect()->back()->with('error', 'У вас нет доступа к восстановлению заказов');
        }

        $order = Order::onlyTrashed()->findOrFail($id);
        $order->restore();
        return redirect()->back()->with('success', 'Заказ успешно восстановлен');
    }
}
