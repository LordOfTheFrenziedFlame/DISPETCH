<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'constructor_id',
        'contract_number',
        'signed_at',
        'comment',
        'final_amount',
        'documentation_due_at',
        'installation_date',
        'installation_days',
        'product_type',
        'ready_date',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'documentation_due_at' => 'date',
        'installation_date' => 'date',
        'ready_date' => 'date',
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function constructor() {
        return $this->belongsTo(User::class, 'constructor_id');
    }

    public function attachments() {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function documentation() {
        return $this->hasOne(Documentation::class, 'order_id', 'order_id');
    }
}

