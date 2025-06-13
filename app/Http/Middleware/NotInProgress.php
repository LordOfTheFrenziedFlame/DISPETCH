<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Order;

class NotInProgress
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $measurement = $request->route('measurement');
        
        // Проверяем, что measurement существует
        if (!$measurement) {
            return redirect()->back()->with('error', 'Замер не найден');
        }

        // Проверяем, что у measurement есть связанный заказ
        if (!$measurement->order_id) {
            return redirect()->back()->with('error', 'Замер не связан с заказом');
        }

        $order = Order::find($measurement->order_id);
        
        // Проверяем, что заказ существует
        if (!$order) {
            return redirect()->back()->with('error', 'Связанный заказ не найден');
        }

        // Проверяем статус заказа
        if ($order->status === 'in_progress') {
            return $next($request);
        }
        
        return redirect()->back()->with('error', 'Заказ не в работе. Действие запрещено.');
    }
}
