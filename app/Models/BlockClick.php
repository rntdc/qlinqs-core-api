<?php

namespace App\Models;

use Database\Factories\BlockClickFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockClick extends Model
{
    /** @use HasFactory<BlockClickFactory> */
    use HasFactory;

    /**
     * block_clicks has no created_at/updated_at columns; clicked_at is a
     * DB-side default instead.
     */
    public $timestamps = false;

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
