<?php

namespace App\Observers;

use App\Models\Installation;
use App\Models\Order;

class InstallationObserver
{
    /**
     * Handle the Installation "created" event.
     */
    public function created(Installation $installation): void
    {
        //
    }

    /**
     * Handle the Installation "updated" event.
     */
    public function updated(Installation $installation): void
    {
        if ($installation->isDirty('installed_at') && !is_null($installation->installed_at)) {
            $order = $installation->order;
            if ($order) {
                $order->status = 'completed';
                $order->save();

                if ($order->contract && is_null($order->contract->signed_at)) {
                    $order->contract->update([
                        'signed_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Handle the Installation "deleted" event.
     */
    public function deleted(Installation $installation): void
    {
        //
    }

    /**
     * Handle the Installation "restored" event.
     */
    public function restored(Installation $installation): void
    {
        //
    }

    /**
     * Handle the Installation "force deleted" event.
     */
    public function forceDeleted(Installation $installation): void
    {
        //
    }
}
