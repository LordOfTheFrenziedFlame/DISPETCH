<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as SupportCollection;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['manager_id', 'customer_name', 'address', 'meeting_at', 'phone_number', 'email', 'order_number', 'status', 'surveyor_id', 'constructor_id', 'installer_id', 'notes', 'total_amount', 'additional_data', 'product_name'];

    protected $casts = [
        'meeting_at' => 'datetime',
    ];

    public function manager() {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function surveyor() {
        return $this->belongsTo(User::class, 'surveyor_id');
    }

    public function constructor() {
        return $this->belongsTo(User::class, 'constructor_id');
    }

    public function installer() {
        return $this->belongsTo(User::class, 'installer_id');
    }

    public function measurement() {
        return $this->hasOne(\App\Models\Measurement::class)->withTrashed();
    }

    public function contract() {
        return $this->hasOne(Contract::class);
    }

    public function documentation() {
        return $this->hasOne(Documentation::class);
    }

    public function production() {
        return $this->hasOne(Production::class);
    }

    public function installation() {
        return $this->hasOne(Installation::class);
    }

    public function attachments() {
        return $this->morphMany(Attachment::class, 'attachable');
    }


    public function getAllAttachmentsAttribute() : SupportCollection {
        // ВНИМАНИЕ: Этот метод может вызывать N+1 проблемы!
        // Убедитесь что используете eager loading: 
        // Order::with(['attachments', 'measurement.attachments', 'contract.attachments', 'documentation.attachments', 'installation.attachments'])
        
        return collect()
            ->merge($this->attachments ?? [])
            ->merge($this->measurement?->attachments ?? [])
            ->merge($this->contract?->attachments ?? [])
            ->merge($this->documentation?->attachments ?? [])
            ->merge($this->installation?->attachments ?? []);
    }

    /**
     * Альтернативный метод для получения всех вложений с правильным eager loading
     */
    public static function withAllAttachments()
    {
        return static::with([
            'attachments',
            'measurement.attachments',
            'contract.attachments', 
            'documentation.attachments',
            'installation.attachments'
        ]);
    }


    public function getProductionStageAttribute()
    {
        if ($this->status === 'completed') {
            return 'Этап завершён: Заказ полностью завершён';
        }

        // --- Финальный этап ---
        if ($this->installation) {
            if ($this->installation->installed_at) {
                return 'Этап завершён: Установка';
            }
            return 'Этап в процессе: Установка';
        }

        // --- Производство ---
        if ($this->production) {
            if ($this->production->completed_at) {
                return 'Этап завершён: Производство';
            }
            return 'Этап в процессе: Производство';
        }

        // --- Документация ---
        if ($this->documentation) {
            if ($this->documentation->completed_at) {
                return 'Этап завершён: Документация';
            }
            return 'Этап в процессе: Документация';
        }

        // --- Договор ---
        if ($this->contract) {
            if ($this->contract->signed_at) {
                return 'Этап завершён: Договор';
            }
            return 'Этап в процессе: Договор';
        }

        // --- Замер ---
        if ($this->measurement) {
            if ($this->measurement->measured_at) {
                return 'Этап завершён: Замер';
            }
            if ($this->measurement->initial_meeting_at) {
                return 'Этап в процессе: Замер';
            }
            return 'Этап в процессе: Заявка';
        }

        // --- Встреча ---
        if ($this->meeting_at) {
            return 'Этап в процессе: Встреча';
        }

        return 'Этап в процессе: Не определён';
    }
}
