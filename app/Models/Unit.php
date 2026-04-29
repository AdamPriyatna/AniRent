<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Unit extends Model
{
    protected $fillable = ['kode_unit', 'nama_unit', 'deskripsi', 'kondisi', 'status', 'foto'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'unit_categories');
    }

    public function bundles(): BelongsToMany
    {
    return $this->belongsToMany(Bundle::class, 'bundle_units');
    } 

    public function bookings(): HasMany
    {
    return $this->hasMany(Booking::class);
    }
}
