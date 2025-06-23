<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Measurement;
use App\Models\Contract;
use App\Models\Documentation;
use App\Models\Installation;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // No action needed on creation
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Синхронизация исполнителей со связанными сущностями
        $this->syncExecutors($order);
        
        if ($order->isDirty('status') && $order->status === 'in_progress') {
            // Если замер уже есть — ничего не делаем
            if ($order->measurement || Measurement::where('order_id', $order->id)->exists()) {
                return;
            }
            
            // Определяем исполнителя для этапа замера
            $surveyorId = $order->surveyor_id;
            
            // Если замерщик не назначен, назначаем текущего пользователя (если это не менеджер)
            if (!$surveyorId && auth('employees')->check()) {
                $currentUser = auth('employees')->user();
                if ($currentUser->role !== 'manager') {
                    $surveyorId = $currentUser->id;
                    // Обновляем заказ с назначенным замерщиком
                    $order->update(['surveyor_id' => $surveyorId]);
                }
            }
            
            // Создаём пустой замер, связанный с этим заказом
            Measurement::create([
                'order_id' => $order->id,
                'surveyor_id' => $surveyorId,
                // 'measured_at' => null, // не указываем, пользователь заполнит позже
            ]);
        }
    }
    
    /**
     * Синхронизация исполнителей между заказом и связанными сущностями
     */
    private function syncExecutors(Order $order): void
    {
        // Синхронизация замерщика
        if ($order->isDirty('surveyor_id')) {
            $measurement = $order->measurement;
            if ($measurement) {
                $oldSurveyorId = $measurement->surveyor_id;
                $measurement->update(['surveyor_id' => $order->surveyor_id]);
                
                Log::info('Синхронизация замерщика в замере', [
                    'order_id' => $order->id,
                    'measurement_id' => $measurement->id,
                    'old_surveyor_id' => $oldSurveyorId,
                    'new_surveyor_id' => $order->surveyor_id
                ]);
            }
        }
        
        // Синхронизация конструктора
        if ($order->isDirty('constructor_id')) {
            // Обновляем в договорах
            $contract = $order->contract;
            if ($contract) {
                $oldConstructorId = $contract->constructor_id;
                $contract->update(['constructor_id' => $order->constructor_id]);
                
                Log::info('Синхронизация конструктора в договоре', [
                    'order_id' => $order->id,
                    'contract_id' => $contract->id,
                    'old_constructor_id' => $oldConstructorId,
                    'new_constructor_id' => $order->constructor_id
                ]);
            }
            
            // Обновляем в документации
            $documentation = $order->documentation;
            if ($documentation) {
                $oldConstructorId = $documentation->constructor_id;
                $documentation->update(['constructor_id' => $order->constructor_id]);
                
                Log::info('Синхронизация конструктора в документации', [
                    'order_id' => $order->id,
                    'documentation_id' => $documentation->id,
                    'old_constructor_id' => $oldConstructorId,
                    'new_constructor_id' => $order->constructor_id
                ]);
            }
        }
        
        // Синхронизация установщика
        if ($order->isDirty('installer_id')) {
            $installation = $order->installation;
            if ($installation) {
                $oldInstallerId = $installation->installer_id;
                $installation->update(['installer_id' => $order->installer_id]);
                
                Log::info('Синхронизация установщика в установке', [
                    'order_id' => $order->id,
                    'installation_id' => $installation->id,
                    'old_installer_id' => $oldInstallerId,
                    'new_installer_id' => $order->installer_id
                ]);
            }
        }

        // Синхронизация вида товара (product_name -> product_type в договоре)
        if ($order->isDirty('product_name')) {
            $contract = $order->contract;
            if ($contract) {
                $oldProductType = $contract->product_type;
                $contract->update(['product_type' => $order->product_name]);

                Log::info('Синхронизация вида товара в договоре', [
                    'order_id'        => $order->id,
                    'contract_id'     => $contract->id,
                    'old_product_type'=> $oldProductType,
                    'new_product_type'=> $order->product_name,
                ]);
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // No action needed on deletion
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        // При восстановлении заказа автоматически восстанавливаем связанные этапы,
        // которые были мягко удалены вместе с заказом.

        $relatedStages = [
            'measurement',      // \App\Models\Measurement (есть SoftDeletes)
            'contract',         // \App\Models\Contract   (есть SoftDeletes)
            'documentation',    // \App\Models\Documentation (есть SoftDeletes)
            'production',       // \App\Models\Production  (при необходимости)
            'installation',     // \App\Models\Installation (при необходимости)
        ];

        foreach ($relatedStages as $relation) {
            $relationObj = $order->{$relation}();

            // Определяем, использует ли связанная модель SoftDeletes
            $relatedClass = $relationObj->getRelated();
            $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive(get_class($relatedClass)));

            // Получаем связанную модель, учитывая soft-deleted
            $instance = $usesSoftDeletes ? $relationObj->withTrashed()->first() : $relationObj->first();

            if ($usesSoftDeletes && $instance && $instance->trashed()) {
                $instance->restore();
            }
        }

        // Логируем восстановление связанных сущностей
        \Illuminate\Support\Facades\Log::info('Восстановлены связанные этапы заказа', [
            'order_id' => $order->id,
            'measurement_restored'   => optional($order->measurement)->exists ? !$order->measurement->trashed() : false,
            'contract_restored'      => optional($order->contract)->exists ? (!$order->contract?->trashed() ?? false) : false,
            'documentation_restored' => optional($order->documentation)->exists ? (!$order->documentation?->trashed() ?? false) : false,
            'production_restored'    => optional($order->production)->exists ? (!$order->production?->trashed() ?? false) : false,
            'installation_restored'  => optional($order->installation)->exists ? (!$order->installation?->trashed() ?? false) : false,
        ]);
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        // No action needed on force deletion
    }
}
