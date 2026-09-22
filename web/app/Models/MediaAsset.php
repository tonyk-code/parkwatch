<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaAsset extends Model
{
    use HasFactory;

    protected $table = 'media_assets';

    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'disk',
        'path',
        'mime',
        'bytes',
        'purpose',
        'related_type',
        'related_id',
        'captured_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'bytes' => 'integer',
            'captured_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}