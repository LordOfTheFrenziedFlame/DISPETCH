<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Measurement extends Model
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'surveyor_id',
        'measured_at',
        'uploaded',
        'additional',
        'notes',
        'status',
        'initial_meeting_at'
    ];

    protected $dates = [
        'measured_at',
        'uploaded',
        'created_at',
        'updated_at',
        'deleted_at',
        'initial_meeting_at'
    ];

    protected $casts = [
        'measured_at' => 'datetime',
        'uploaded' => 'datetime',
        'initial_meeting_at' => 'datetime',
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function surveyor() {
        return $this->belongsTo(User::class, 'surveyor_id');
    }

    public function attachments() {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}

