<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedPhone extends Model
{
    protected $fillable = ['phone', 'reason'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}