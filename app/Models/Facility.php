<?php

namespace App\Models;

use App\Enums\FacilityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'location',
        'capacity',
        'description',
        'image_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => FacilityStatus::class,
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
