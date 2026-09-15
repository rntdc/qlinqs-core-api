<?php

namespace App\Validation;

use Illuminate\Validation\Rule;

/**
 * Validation rules for pages.content (qlinqs-estrutura-de-dados.md §3-§6).
 *
 * `layout`'s enum is conditional on the block's `type` — only `link` blocks
 * have a defined layout enum (§5.1); other v1 types just need a non-empty
 * string. That can't be expressed as one static wildcard rule, so it's
 * computed per block index from the actual payload.
 *
 * `card` is required to be present but is allowed to be an empty object
 * (`present`, not `required`): every one of its fields is optional per the
 * spec, so a strict Laravel `required` (which also rejects empty arrays)
 * would make a minimal/decorative card impossible to submit.
 */
class PageContentRules
{
    public const V1_BLOCK_TYPES = ['link', 'whatsapp', 'maps', 'text', 'heading'];

    public const LINK_LAYOUTS = ['button', 'thumbnail', 'featured'];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function rules(array $data): array
    {
        $rules = [
            'header' => 'required|array',
            'header.name' => 'required|string',
            'header.bio' => 'nullable|string',

            // Present (not required): the section always exists but the
            // list of icons can legitimately be empty.
            'socialIcons' => 'present|array',
            'socialIcons.*.platform' => 'required|string',
            'socialIcons.*.value' => 'required|string',

            // Present (not required): the block list can legitimately be empty.
            'blocks' => 'present|array',
            'blocks.*.id' => 'required|string',
            'blocks.*.kind' => ['required', Rule::in(['atomic'])],
            'blocks.*.type' => ['required', Rule::in(self::V1_BLOCK_TYPES)],
            'blocks.*.hidden' => 'required|boolean',
            'blocks.*.card' => 'present|array',

            'blocks.*.card.title' => 'nullable|string',
            'blocks.*.card.description' => 'nullable|string',
            'blocks.*.card.buttonText' => 'nullable|string',
            'blocks.*.card.label' => 'nullable|string',

            'blocks.*.card.link' => 'nullable|array',
            'blocks.*.card.link.kind' => ['required_with:blocks.*.card.link', Rule::in(['url', 'email'])],
            'blocks.*.card.link.href' => 'required_with:blocks.*.card.link|string',

            // Container-only requiredness (image mandatory inside carousel/
            // grid cards) doesn't apply in v1 — containers don't exist yet.
            'blocks.*.card.image' => 'nullable|array',
            'blocks.*.card.image.source' => ['required_with:blocks.*.card.image', Rule::in(['upload', 'icon', 'emoji'])],
            'blocks.*.card.image.value' => 'required_with:blocks.*.card.image|string',

            'blocks.*.card.overrides' => 'nullable|array',
        ];

        $rules += StyleOverrideRules::rules('blocks.*.card.overrides', allowAlignAndSize: true);

        foreach ((array) ($data['blocks'] ?? []) as $index => $block) {
            $type = is_array($block) ? ($block['type'] ?? null) : null;

            $rules["blocks.$index.layout"] = $type === 'link'
                ? ['required', Rule::in(self::LINK_LAYOUTS)]
                : 'required|string';
        }

        return $rules;
    }
}
