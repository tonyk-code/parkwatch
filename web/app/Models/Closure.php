<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Closure extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'closeable_type',
        'closeable_id',
        'reason',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function closeable(): MorphTo
    {
        return $this->morphTo();
    }
}