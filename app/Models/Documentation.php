<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documentation extends Model
{

    use SoftDeletes;

    protected $fillable = ['order_id', 'constructor_id', 'description', 'completed_at'];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function constructor() {
        return $this->belongsTo(User::class, 'constructor_id');
    }

    public function attachments() {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function installation() {
        return $this->hasOne(Installation::class, 'order_id', 'order_id');
    }
}
