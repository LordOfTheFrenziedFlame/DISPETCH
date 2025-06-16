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
        // Если замер завершен (статус изменился на COMPLETED) и у заказа еще нет договора
        if ($measurement->isDirty('status') && $measurement->status === \App\Models\Measurement::STATUS_COMPLETED) {
            $order = $measurement->order;

            if ($order && !$order->contract) {
                Contract::create([
                    'order_id' => $order->id,
                    'contract_number' => null,
                    // Другие поля по умолчанию, если необходимо
                ]);
            }
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
