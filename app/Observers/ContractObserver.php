<?php

namespace App\Observers;

use App\Models\Contract;
use App\Models\Documentation;
use App\Models\Production;
use App\Models\Installation;

class ContractObserver
{
    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        $this->handleStageCreation($contract);
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        $this->handleStageCreation($contract);
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

    /**
     * Создание связанных этапов (Documentation, Production, Installation),
     * если в договоре указаны соответствующие даты и исполнители.
     */
    protected function handleStageCreation(Contract $contract): void
    {
        // 1. Документация
        if ($contract->documentation_due_at && !$contract->documentation) {
            $constructorId = $contract->constructor_id ?? $contract->order->constructor_id;

            // fallback: если оба null и в сессии сотрудник-конструктор — назначаем его
            if (!$constructorId && auth('employees')->check()) {
                $user = auth('employees')->user();
                if ($user->role === 'constructor') {
                    $constructorId = $user->id;
                }
            }

            Documentation::create([
                'order_id' => $contract->order_id,
                'constructor_id' => $constructorId,
                'description' => 'Автоматически создано из договора',
            ]);
        }

        // 2. Производство / готовность (если нужна)
        if ($contract->installation_date && !$contract->order->production) {
            Production::create([
                'order_id' => $contract->order_id,
            ]);
        }

        // 3. Установка
        if ($contract->installation_date) {
            $installation = $contract->order->installation; // может быть null

            // если установки ещё нет — создаём одну запись
            if (!$installation) {
                Installation::create([
                    'order_id'     => $contract->order_id,
                    'installer_id' => $contract->order->installer_id, // может быть null
                ]);
            } else if ($contract->order->installer_id && is_null($installation->installer_id)) {
                // установка уже есть, но в ней не указан исполнитель — обновляем
                $installation->update(['installer_id' => $contract->order->installer_id]);
            }
        }

        // --- СИНХРОНИЗАЦИЯ исполнителей при изменении
        // Обновляем конструктора в существующей документации
        if ($contract->isDirty('constructor_id') && $contract->documentation) {
            $contract->documentation->update([
                'constructor_id' => $contract->constructor_id,
            ]);
        }

        // Если сменили установщика в заказе, то синхронизируем установку
        if ($contract->order->wasChanged('installer_id') && $contract->order->installation) {
            $contract->order->installation->update([
                'installer_id' => $contract->order->installer_id,
            ]);
        }
    }
}
