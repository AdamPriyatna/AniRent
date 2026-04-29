<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    protected $fillable = [
        'kode_booking', 'user_id', 'unit_id', 'bundle_id',
        'tanggal_mulai', 'tanggal_selesai_rencana',
        'tanggal_selesai_aktual', 'status', 'diproses_oleh'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai_rencana' => 'date',
        'tanggal_selesai_aktual' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class);
    }
}
