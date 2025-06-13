<?php

namespace App\Observers;

use App\Models\Production;
use App\Models\Installation;
use Illuminate\Support\Facades\Log;

class ProductionObserver
{
    /**
     * Handle the Production "created" event.
     */
    public function created(Production $production): void
    {
        //
    }

    /**
     * Handle the Production "updated" event.
     */
    public function updated(Production $production): void
    {
        if ($production->isDirty('completed_at') && !is_null($production->completed_at)) {
            $order = $production->order;

            // Проверяем, не создана ли уже установка
            if ($order && !$order->installation) {
                Installation::create([
                    'order_id' => $order->id,
                    'installer_id' => $order->installer_id,
                    'documentation_id' => $order->documentation->id ?? null,
                ]);

                Log::info('Автоматически создана установка', [
                    'production_id' => $production->id,
                    'order_id' => $order->id,
                    'installer_id' => $order->installer_id
                ]);
            }
        }
    }

    /**
     * Handle the Production "deleted" event.
     */
    public function deleted(Production $production): void
    {
        //
    }

    /**
     * Handle the Production "restored" event.
     */
    public function restored(Production $production): void
    {
        //
    }

    /**
     * Handle the Production "force deleted" event.
     */
    public function forceDeleted(Production $production): void
    {
        //
    }
}
