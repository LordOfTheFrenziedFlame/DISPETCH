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
        ->withoutTrashed()
        ->get();

        $attachments = Attachment::whereHasMorph(
            'attachable',
            [Contract::class],
        )->get();

        return view('dashboard.contract.index', compact('contracts', 'attachments'));
    }

    public function create()
    {
        if (!$this->canManageContracts()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию договоров');
        }
        
        $orders = \App\Models\Order::where('status', '!=', 'completed')->get(['id', 'order_number', 'customer_name']);
        
        return view('dashboard.contract.create', compact('orders'));
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
        return view('dashboard.contract.edit', compact('contract'));
    }

    public function update(Request $request, Contract $contract)
    {
        if (!$this->canManageContracts()) {
            return redirect()->back()->with('error', 'У вас нет доступа к обновлению договоров');
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'constructor_id' => 'nullable|exists:users,id',
            'contract_number' => 'required|string|max:255|unique:contracts,contract_number,' . $contract->id,
            'signed_at' => 'nullable|date',
            'comment' => 'nullable|string|max:1000',
        ]);

        $contract->update($request->all());
        return redirect()->route('employee.contracts.index')->with('success', 'Договор успешно обновлен');
    }

    public function store(Request $request)
    {
        if (!$this->canManageContracts()) {
            return redirect()->back()->with('error', 'У вас нет доступа к созданию договоров');
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'contract_number' => 'required|string|unique:contracts,contract_number|max:255',
            'signed_at' => 'nullable|date',
        ]);

        $contract = Contract::create($request->only(['order_id', 'contract_number', 'signed_at']));
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
            'signed_file.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx|max:10240',
            'comment'       => 'nullable|string|max:1000',
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
            if ($request->filled('comment')) {
                $contract->comment = $request->input('comment');
            }
            $contract->save();
            return redirect()->route('employee.contracts.index')->with('success', 'Договор успешно подписан');
        }
        return redirect()->back()->with('error', 'Ошибка при подписании договора');
    }
}
