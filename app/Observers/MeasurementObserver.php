<?php

namespace App\Observers;

use App\Models\Measurement;
use App\Models\Contract;

class MeasurementObserver
{
    /**
     * Handle the Measurement "created" event.
     */
    public function created(Measurement $measurement): void
    {
        //
    }

    /**
     * Handle the Measurement "updated" event.
     */
    public function updated(Measurement $measurement): void
    {
        if($measurement->status === 'completed') {
            if ($measurement->contract || Contract::where('order_id', $measurement->order_id)->exists()) {
                return;
            }

            // Определяем исполнителя для следующего этапа
            $constructorId = $measurement->order->constructor_id;
            
            // Если конструктор не назначен, назначаем текущего пользователя (если это не менеджер)
            if (!$constructorId && auth('employees')->check()) {
                $currentUser = auth('employees')->user();
                if ($currentUser->role !== 'manager') {
                    $constructorId = $currentUser->id;
                    // Обновляем заказ с назначенным конструктором
                    $measurement->order->update(['constructor_id' => $constructorId]);
                }
            }

            $contract = Contract::create([
                'measurement_id' => $measurement->id,
                'order_id' => $measurement->order_id,
                'contract_number' => $measurement->order->order_number,
                'constructor_id' => $constructorId,
            ]);
        }
    }

    /**
     * Handle the Measurement "deleted" event.
     */
    public function deleted(Measurement $measurement): void
    {
        //
    }

    /**
     * Handle the Measurement "restored" event.
     */
    public function restored(Measurement $measurement): void
    {
        //
    }

    /**
     * Handle the Measurement "force deleted" event.
     */
    public function forceDeleted(Measurement $measurement): void
    {
        //
    }
}
