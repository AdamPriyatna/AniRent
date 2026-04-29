<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Bundle extends Model
{
    protected $fillable = ['nama_bundle', 'deskripsi', 'status', 'harga_per_hari', 'foto'];

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'bundle_units');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
