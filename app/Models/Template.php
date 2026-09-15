<?php

namespace App\Models;

use Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Intentionally no relationship to Page: applying a template copies its
// theme into pages.theme, it doesn't link to it (qlinqs-estrutura-banco.md §3.4).
class Template extends Model
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory, HasUuids;

    /**
     * templates has no updated_at column.
     */
    const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'theme' => 'array',
        ];
    }
}
