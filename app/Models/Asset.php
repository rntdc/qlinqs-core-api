<?php

namespace App\Models;

use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory, HasUuids;

    /**
     * assets has no updated_at column.
     */
    const UPDATED_AT = null;

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
