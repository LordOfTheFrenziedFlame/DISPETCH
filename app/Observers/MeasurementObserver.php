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
        // Если замер завершен (появилась дата measured_at) и у заказа еще нет договора
        if ($measurement->isDirty('measured_at') && !is_null($measurement->measured_at)) {
            $order = $measurement->order;

            if ($order && !$order->contract) {
                Contract::create([
                    'order_id' => $order->id,
                    'contract_number' => 'CN-' . $order->id . '-' . now()->timestamp,
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
