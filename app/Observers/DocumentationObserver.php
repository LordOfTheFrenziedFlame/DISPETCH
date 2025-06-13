<?php

namespace App\Observers;

use App\Models\Documentation;
use App\Models\Installation;
use App\Models\Production;

class DocumentationObserver
{
    /**
     * Handle the Documentation "created" event.
     */
    public function created(Documentation $documentation): void
    {
        //
    }

    /**
     * Handle the Documentation "updated" event.
     */
    public function updated(Documentation $documentation): void
    {
        if ($documentation->isDirty('completed_at') && !is_null($documentation->completed_at)) {
            // Проверяем, не создано ли уже производство для этого заказа
            if (Production::where('order_id', $documentation->order_id)->exists()) {
                return;
            }

            // Создаем запись о производстве
            Production::create([
                'order_id' => $documentation->order_id,
            ]);
            
            // Меняем статус заказа на "в производстве"
            $order = $documentation->order;
            if ($order) {
                $order->status = 'in_progress';
                $order->save();
            }
        }
    }

    /**
     * Handle the Documentation "deleted" event.
     */
    public function deleted(Documentation $documentation): void
    {
        //
    }

    /**
     * Handle the Documentation "restored" event.
     */
    public function restored(Documentation $documentation): void
    {
        //
    }

    /**
     * Handle the Documentation "force deleted" event.
     */
    public function forceDeleted(Documentation $documentation): void
    {
        //
    }
}
