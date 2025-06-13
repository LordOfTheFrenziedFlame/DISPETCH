<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = ['filename', 'path', 'mime_type', 'attachable_type', 'attachable_id', 'comment'];

    public function attachable() {
        return $this->morphTo();
    }
}

