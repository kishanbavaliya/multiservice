<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class DriverLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'driver_id' => 'integer',
        'lat' => 'double',
        'lng' => 'double',
        'rotation' => 'double',
    ];
    protected $fillable = [
        'driver_id',
        'lat',
        'lng',
        'rotation'
    ];


    public function driver()
    {
        return $this->belongsTo('App\Models\User', 'driver_id', 'id')->withTrashed();
    }


    // Helpful method to check if location is valid
    public function isValidLocation(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null
            && $this->latitude >= -90
            && $this->latitude <= 90
            && $this->longitude >= -180
            && $this->longitude <= 180;
    }

    // Accessor for formatted coordinates
    public function getFormattedCoordinatesAttribute()
    {
        return Str::of($this->latitude)
            ->append(', ')
            ->append($this->longitude);
    }

    // Scope to find locations within a certain radius
    public function scopeWithinRadius($query, $latitude, $longitude, $radiusInKm = 10)
    {
        // Haversine formula for calculating distance
        return $query->select('*')
            ->selectRaw('
                6371 * 2 * ASIN(
                    SQRT(
                        POWER(SIN((' . $latitude . ' - latitude) * PI()/180 / 2), 2) +
                        COS(' . $latitude . ' * PI()/180) * COS(latitude * PI()/180) *
                        POWER(SIN((' . $longitude . ' - longitude) * PI()/180 / 2), 2)
                    )
                ) as distance
            ')
            ->havingRaw('distance <= ?', [$radiusInKm])
            ->orderBy('distance');
    }
}