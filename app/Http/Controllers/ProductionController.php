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
    public function index()
    {
        $productions = Production::with('order')->whereNull('completed_at')->get();
        return view('dashboard.productions.index', compact('productions'));
    }

    public function complete(Production $production, Request $request)
    {
        $request->validate(['notes' => 'nullable|string']);
        
        $production->update([
            'completed_at' => now(),
            'notes' => $request->notes,
        ]);

        return redirect()->route('employee.productions.index')->with('success', 'Производство для заказа №' . $production->order->order_number . ' завершено.');
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
