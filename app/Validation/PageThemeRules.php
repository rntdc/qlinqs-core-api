<?php

namespace App\Validation;

use Illuminate\Validation\Rule;

/**
 * Validation rules for pages.theme / templates.theme (qlinqs-estrutura-de-dados.md §7).
 *
 * The doc doesn't name a discriminator key for `page.background`'s type —
 * we chose `background.type` ourselves; that's our naming, not the spec's.
 *
 * `page.profilePicture`, `blockDefaults` and `fonts` are required to be
 * present but allowed to be empty objects (`present`, not `required`):
 * every one of their fields is optional (the profile picture itself is
 * optional per §7.1), so a strict Laravel `required` (which also rejects
 * empty arrays) would make "no overrides" / "no picture" impossible to
 * submit.
 */
class PageThemeRules
{
    public const HEADER_LAYOUT = ['classic', 'business'];

    public const BACKGROUND_TYPE = ['none', 'solid', 'gradient'];

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        $rules = [
            'page' => 'required|array',
            'page.header' => 'required|array',
            'page.header.layout' => ['required', Rule::in(self::HEADER_LAYOUT)],
            'page.background' => 'required|array',
            'page.background.type' => ['required', Rule::in(self::BACKGROUND_TYPE)],
            'page.profilePicture' => 'present|array',

            'blockDefaults' => 'present|array',

            'fonts' => 'present|array',
            'fonts.titleFont' => 'nullable|string',
            'fonts.textFont' => 'nullable|string',

            'palette' => 'required|array',
            'palette.background' => 'required|string',
            'palette.text' => 'required|string',
            'palette.surface' => 'required|string',
            'palette.onSurface' => 'required|string',
            'palette.accent' => 'required|string',
            'palette.onAccent' => 'required|string',
        ];

        return $rules + StyleOverrideRules::rules('blockDefaults', allowAlignAndSize: false);
    }
}
