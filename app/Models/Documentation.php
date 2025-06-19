<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documentation extends Model
{

    use SoftDeletes;

    protected $fillable = ['order_id', 'constructor_id', 'description', 'completed_at', 'notes'];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function constructor() {
        return $this->belongsTo(User::class, 'constructor_id');
    }

    public function attachments() {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    // Получить контракт через заказ
    public function getContractAttribute() {
        return $this->order ? $this->order->contract : null;
    }
}
