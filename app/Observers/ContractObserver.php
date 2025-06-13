<?php

namespace App\Observers;

use App\Models\Contract;
use App\Models\Documentation;

class ContractObserver
{
    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        if($contract->signed_at) {
            if($contract->documentation || Documentation::where('order_id', $contract->order_id)->exists()) {
                return; 
            }
            
            // Определяем исполнителя для этапа документации
            $constructorId = $contract->order->constructor_id;
            
            // Если конструктор не назначен, назначаем текущего пользователя (если это не менеджер)
            if (!$constructorId && auth('employees')->check()) {
                $currentUser = auth('employees')->user();
                if ($currentUser->role !== 'manager') {
                    $constructorId = $currentUser->id;
                    // Обновляем заказ с назначенным конструктором
                    $contract->order->update(['constructor_id' => $constructorId]);
                }
            }
            
            Documentation::create([
                'order_id' => $contract->order_id,
                'constructor_id' => $constructorId ?? $contract->constructor_id ?? 1,
                'completed_at' => null,
            ]);
        }
    }

    /**
     * Handle the Contract "deleted" event.
     */
    public function deleted(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "restored" event.
     */
    public function restored(Contract $contract): void
    {
        //
    }

    /**
     * Handle the Contract "force deleted" event.
     */
    public function forceDeleted(Contract $contract): void
    {
        //
    }
}
