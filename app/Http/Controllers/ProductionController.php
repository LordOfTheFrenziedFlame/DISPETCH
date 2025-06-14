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
        $productions = Production::with(['order', 'order.installer'])->whereNull('completed_at')->get();
        $employees = \App\Models\User::where('role', 'manager')->get(['id', 'name', 'role']);
        $selectedEmployee = $request->get('manager_id') ? \App\Models\User::find($request->get('manager_id')) : null;
        
        return view('dashboard.productions.index', compact('productions', 'employees', 'selectedEmployee'));
    }

    public function complete(Production $production, Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $production->load('order');

        // Проверяем, что у заказа назначен установщик
        if (!$production->order->installer_id) {
            return redirect()->back()->with('error', 'Для завершения производства необходимо назначить установщика в заказе №' . $production->order->order_number);
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
