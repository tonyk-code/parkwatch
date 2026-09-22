<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'organization_id',
        'plate_normalised',
        'plate_display',
        'plate_region',
        'vehicle_type',
        'make',
        'model',
        'colour',
        'owner_user_id',
        'is_blacklisted',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_blacklisted' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function parkingSessions(): HasMany
    {
        return $this->hasMany(ParkingSession::class);
    }

    public function permits(): HasMany
    {
        return $this->hasMany(Permit::class);
    }

    public function plateReads(): HasMany
    {
        return $this->hasMany(PlateRead::class, 'matched_vehicle_id');
    }

    // The ERD contains owner_user_id but does not define
    // a User → Vehicle relationship. We can add it later
    // if the application needs vehicle ownership navigation.
}