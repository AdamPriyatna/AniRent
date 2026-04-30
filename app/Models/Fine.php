<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    protected $fillable = ['booking_id', 'hari_terlambat', 'jumlah_denda', 'status'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
