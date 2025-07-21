<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use App\Components\SaveMedia as SaveMediaService;
use App\Models\Attachment;
use App\Traits\HasRolePermissions;

class ContractController extends Controller
{
    use HasRolePermissions;

    public function index()
    {
        if (!$this->canViewContracts()) {
            return redirect()->route('employee.orders.index')->with('error', 'У вас нет доступа к просмотру договоров');
        }

        $contracts = Contract::latest()
        ->whereHas('order', function ($query) {
            $query->whereNull('deleted_at');
        })
        ->with(\App\Models\Order::ORDER_ATTACHMENTS_RELATIONS)
        ->withoutTrashed()
        ->get();

        $attachments = Attachment::whereHasMorph(
            'attachable',
            [Contract::class],
        )->get();

        $employees = \App\Models\User::all(['id', 'name', 'role']);
        $managers = \App\Models\User::where('role', 'manager')->get(['id', 'name']);
        $constructorsList = \App\Models\User::all(['id','name']);
        $installersList = \App\Models\User::all(['id','name']);
        $selectedEmployee = request('manager_id') ? \App\Models\User::find(request('manager_id')) : null;

        return view('dashboard.contract.index', compact('contracts', 'attachments', 'employees', 'managers', 'selectedEmployee', 'constructorsList', 'installersList'));
    }

    public function create()
    {
        if (!$this->canManageContracts()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию договоров');
        }
        
        $orders = \App\Models\Order::where('status', '!=', 'completed')->get(['id', 'order_number', 'customer_name']);
        $constructors = \App\Models\User::where('role', 'constructor')->get(['id', 'name']);
        
        return view('dashboard.contract.create', compact('orders', 'constructors'));
    }

    public function show(Contract $contract)
    {
        if (!$this->canViewContracts()) {
            return redirect()->back()->with('error', 'У вас нет доступа к просмотру договоров');
        }
        return view('dashboard.contract.show', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        if (!$this->canManageContracts()) {
            return redirect()->back()->with('error', 'У вас нет доступа к редактированию договоров');
        }
        
        $orders = \App\Models\Order::all(['id', 'order_number', 'customer_name']);
        $constructors = \App\Models\User::where('role', 'constructor')->get(['id', 'name']);
        
        return view('dashboard.contract.edit', compact('contract', 'orders', 'constructors'));
    }

    public function update(Request $request, Contract $contract)
    {
        if (!$this->canManageContracts()) {
            return redirect()->back()->with('error', 'У вас нет доступа к обновлению договоров');
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'constructor_id' => 'required|exists:users,id',
            'contract_number' => 'nullable|string|max:255|unique:contracts,contract_number,' . $contract->id,
            'signed_at' => 'nullable|date',
            'comment' => 'nullable|string|max:1000',
            'final_amount' => 'nullable|numeric|min:0',
            'documentation_due_at' => 'nullable|date',
            'installation_date' => 'nullable|date',
            'installer_id' => 'required|exists:users,id',
            'product_type' => 'nullable|string|max:255',
            'ready_date' => 'nullable|date',
        ]);

        // Если product_type не отправлен – берём из заказа
        $data = $request->all();
        if (!array_key_exists('product_type', $data) || is_null($data['product_type'])) {
            $data['product_type'] = $contract->order->product_name;
        }

        $contract->update($data);
        if($request->filled('installer_id')) {
            $contract->order->update(['installer_id' => $request->input('installer_id')]);
        }
        return redirect()->route('employee.contracts.index')->with('success', 'Договор успешно обновлен');
    }

    public function store(Request $request)
    {
        if (!$this->canManageContracts()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию договоров');
        }

        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'contract_number' => 'nullable|string|unique:contracts,contract_number|max:255',
            'signed_at' => 'nullable|date',
            'constructor_id' => 'required|exists:users,id',
            'final_amount' => 'nullable|numeric|min:0',
            'documentation_due_at' => 'nullable|date',
            'installation_date' => 'nullable|date',
            'installer_id' => 'required|exists:users,id',
            'product_type' => 'nullable|string|max:255',
            'ready_date' => 'nullable|date',
        ]);

        // Автозаполнение product_type из заказа, если не передано
        $order = \App\Models\Order::find($validated['order_id']);
        $productType = $request->filled('product_type') ? $request->input('product_type') : ($order->product_name ?? null);

        $contract = Contract::create(array_merge($request->only([
            'order_id',
            'constructor_id',
            'contract_number',
            'signed_at',
            'comment',
            'final_amount',
            'documentation_due_at',
            'installation_date',
            'installer_id',
            'ready_date',
        ]), ['product_type' => $productType]));
        if($request->filled('installer_id')) {
            $contract->order->update(['installer_id' => $request->input('installer_id')]);
        }
        return redirect()->route('employee.contracts.index')->with('success', 'Договор успешно создан');
    }

    public function destroy(Contract $contract)
    {
        if (!$this->canManageContracts()) {
            return redirect()->back()->with('error', 'У вас нет доступа к удалению договоров');
        }

        $contract->delete();
        return redirect()->route('employee.contracts.index')->with('success', 'Договор успешно удален');
    }

    public function sign(Request $request, Contract $contract)
    {
        if (!$this->canManageContracts()) {
            return redirect()->back()->with('error', 'У вас нет доступа к подписанию договоров');
        }

        if($contract->signed_at) {
            return redirect()->back()->with('error', 'Договор уже подписан');
        }

        $request->validate([
            'signed_file'   => 'required',
            'signed_file.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,dbs|max:10240',
            'comment'       => 'nullable|string|max:1000',
            'final_amount'  => 'nullable|numeric|min:0',
            'documentation_due_at' => 'nullable|date',
            'installation_date'    => 'nullable|date',
            'constructor_id' => 'required|exists:users,id',
            'installer_id' => 'required|exists:users,id',
            'product_type' => 'nullable|string|max:255',
            'ready_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'contract_number' => 'nullable|string|max:255',
            
        ]);

        // Получаем файлы как массив (даже если один)
        $files = $request->file('signed_file');
        if (!is_array($files)) {
            $files = [$files];
        }

        $service = app(SaveMediaService::class);    
        $service->attachable_type = Contract::class; 
        $service->attachable_id = $contract->id;
        $service->file = $files; // всегда массив файлов
        $success = $service->save();    

        if($success) {
            // Если конструктор не назначен и текущий пользователь не менеджер, назначаем текущего пользователя
            if (!$contract->order->constructor_id && !$this->isManager()) {
                $contract->order->update(['constructor_id' => $this->getCurrentUser()->id]);
            }
            
            $contract->signed_at = now();
            // Обновляем дополнительные поля, влияющие на Observer
            $updateData = $request->only([
                'comment',
                'final_amount',
                'documentation_due_at',
                'installation_date',
                'constructor_id',
                'installer_id',
                'product_type',
                'ready_date',
                'notes',
                'contract_number',
            ]);

            if (empty($updateData['product_type'])) {
                $updateData['product_type'] = $contract->order->product_name;
            }

            $contract->fill($updateData);
            $contract->save();
            if($request->filled('installer_id')) {
                $contract->order->update(['installer_id' => $request->input('installer_id')]);
            }
            return redirect()->route('employee.contracts.index')->with('success', 'Договор успешно подписан');
        }
        return redirect()->back()->with('error', 'Ошибка при подписании договора');
    }
}
