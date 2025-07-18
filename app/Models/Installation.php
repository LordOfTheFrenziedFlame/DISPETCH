<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installation extends Model
{
    use SoftDeletes;
    protected $fillable = ['order_id', 'documentation_id', 'installer_id', 'installed_at', 'result_notes'];

    protected $casts = [
        'installed_at' => 'datetime',
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function installer() {
        return $this->belongsTo(User::class, 'installer_id');
    }

    public function documentation() {
        return $this->belongsTo(Documentation::class);
    }
}

