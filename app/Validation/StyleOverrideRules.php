<?php

namespace App\Validation;

use Illuminate\Validation\Rule;

/**
 * Style override fields shared between a block's card.overrides
 * (qlinqs-estrutura-de-dados.md §5.3) and theme.blockDefaults (§7.3) — same
 * shape, minus `align`/`size`, which are block-only and never belong in
 * blockDefaults.
 */
class StyleOverrideRules
{
    public const TACTILE = ['flat', 'concave', 'convex', 'inset', 'glass', 'none'];

    public const SHADOW_STYLE = ['soft', 'solid'];

    public const ALIGN = ['left', 'center', 'right'];

    public const SIZE = ['large', 'small'];

    /**
     * @return array<string, mixed>
     */
    public static function rules(string $prefix, bool $allowAlignAndSize): array
    {
        $rules = [
            "$prefix.tactile" => ['nullable', Rule::in(self::TACTILE)],
            "$prefix.color" => 'nullable|string',
            "$prefix.textColor" => 'nullable|string',
            "$prefix.corner" => 'nullable|integer|min:0|max:100',
            "$prefix.border" => 'nullable|integer|min:0|max:100',
            "$prefix.borderColor" => 'nullable|string',
            "$prefix.shadow" => 'nullable|integer|min:0|max:100',
            "$prefix.shadowStyle" => ['nullable', Rule::in(self::SHADOW_STYLE)],
            "$prefix.spacing" => 'nullable|integer|min:0|max:100',
        ];

        // align/size are block-only (never global), so blockDefaults rejects
        // them outright instead of just not defining them — an absent rule
        // would silently allow them through as unvalidated extra keys.
        if ($allowAlignAndSize) {
            $rules["$prefix.align"] = ['nullable', Rule::in(self::ALIGN)];
            $rules["$prefix.size"] = ['nullable', Rule::in(self::SIZE)];
        } else {
            $rules["$prefix.align"] = 'prohibited';
            $rules["$prefix.size"] = 'prohibited';
        }

        return $rules;
    }
}
