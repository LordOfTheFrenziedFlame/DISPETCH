<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Production;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $productions = Production::with(array_merge([
            'order', 
            'order.installer'
        ], \App\Models\Order::ORDER_ATTACHMENTS_RELATIONS))
            ->whereHas('order', function($q){
                $q->whereNull('deleted_at');
            })
            ->get();
        $employees = \App\Models\User::where('role', 'manager')->get(['id', 'name', 'role']);
        $managers = \App\Models\User::where('role', 'manager')->get(['id', 'name']);
        $selectedEmployee = $request->get('manager_id') ? \App\Models\User::find($request->get('manager_id')) : null;
        
        return view('dashboard.productions.index', compact('productions', 'employees', 'managers', 'selectedEmployee'));
    }

    public function complete(Production $production, Request $request)
    {

        if(auth('employees')->user()->role !== 'manager'){
            return redirect()->back()->with('error', 'У вас нет доступа к завершению производства');
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $production->loadMissing('order.documentation');
        $order = $production->order;

        // Проверяем последовательность конвейера: документация должна быть завершена
        if (!$order->documentation || is_null($order->documentation->completed_at)) {
            return redirect()->back()->with('error', 'Невозможно завершить производство, пока документация не завершена для заказа №' . $order->order_number);
        }

        // Проверяем, что у заказа назначен установщик
        if (!$order->installer_id) {
            return redirect()->back()->with('error', 'Для завершения производства необходимо назначить установщика в заказе №' . $order->order_number);
        }
        
        $production->update([
            'completed_at' => now(),
            'notes' => $request->notes,
        ]);

        // Installation будет создана автоматически через ProductionObserver

        return redirect()->route('employee.productions.index')->with('success', 'Производство для заказа №' . $production->order->order_number . ' завершено. Создана задача на установку.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
