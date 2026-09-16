<?php

namespace App\Validation;

use Illuminate\Validation\Rule;

/**
 * Validation rules for pages.content (qlinqs-estrutura-de-dados.md §3-§6).
 *
 * Several rules are conditional on a block's `kind`/`type` and can't be
 * expressed as static wildcard rules, so they're computed per block index
 * from the actual payload:
 * - `layout`: required on every block; enum `LINK_LAYOUTS` only when
 *   `type === "link"` (§5.1); any non-empty string otherwise; not required
 *   at all for container blocks (they have no `layout` concept, §5.2).
 * - `type`: enum depends on `kind` — the 5 v1 atomic types for
 *   `kind: "atomic"`, `carousel|grid` for `kind: "container"`.
 * - `card`/`hidden`: `card` is only meaningful (and required-present) for
 *   atomic blocks; containers use `config`/`items` instead. `hidden` stays
 *   required for atomic blocks but is optional for containers.
 * - `config.size`/`config.columns`: which one is required (and which is
 *   prohibited) depends on the container's `type`.
 *
 * `card`/`items.*` are required to be *present* but allowed to be empty
 * objects/arrays (`present`, not `required`): most of their fields are
 * optional per the spec, so a strict Laravel `required` (which also rejects
 * empty arrays) would make a minimal/decorative card, or a container the
 * editor hasn't filled in yet, impossible to submit.
 *
 * Containers never contain containers (principle 1.2): an `items` entry is
 * a card, so `kind`/`type`/`items` are explicitly `prohibited` on it rather
 * than just left unvalidated.
 */
class PageContentRules
{
    public const V1_BLOCK_TYPES = ['link', 'whatsapp', 'maps', 'text', 'heading'];

    public const LINK_LAYOUTS = ['button', 'thumbnail', 'featured'];

    public const CONTAINER_TYPES = ['carousel', 'grid'];

    public const CAROUSEL_SIZES = ['large', 'small'];

    public const GRID_COLUMNS = [2, 3];

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
            'blocks.*.kind' => ['required', Rule::in(['atomic', 'container'])],

            // Atomic block's card fields — wildcard-safe: these simply don't
            // match anything on a container block, which has no `card` key.
            'blocks.*.card.title' => 'nullable|string',
            'blocks.*.card.description' => 'nullable|string',
            'blocks.*.card.buttonText' => 'nullable|string',
            'blocks.*.card.label' => 'nullable|string',

            'blocks.*.card.link' => 'nullable|array',
            'blocks.*.card.link.kind' => ['required_with:blocks.*.card.link', Rule::in(['url', 'email'])],
            'blocks.*.card.link.href' => 'required_with:blocks.*.card.link|string',

            'blocks.*.card.image' => 'nullable|array',
            'blocks.*.card.image.source' => ['required_with:blocks.*.card.image', Rule::in(['upload', 'icon', 'emoji'])],
            'blocks.*.card.image.value' => 'required_with:blocks.*.card.image|string',

            'blocks.*.card.overrides' => 'nullable|array',

            // Container items are cards, same shape as an atomic card,
            // except `image` is always required (§5.2: "imagem obrigatória
            // em todo card de container").
            'blocks.*.items.*.title' => 'nullable|string',
            'blocks.*.items.*.description' => 'nullable|string',
            'blocks.*.items.*.buttonText' => 'nullable|string',
            'blocks.*.items.*.label' => 'nullable|string',

            'blocks.*.items.*.link' => 'nullable|array',
            'blocks.*.items.*.link.kind' => ['required_with:blocks.*.items.*.link', Rule::in(['url', 'email'])],
            'blocks.*.items.*.link.href' => 'required_with:blocks.*.items.*.link|string',

            'blocks.*.items.*.image' => 'required|array',
            'blocks.*.items.*.image.source' => ['required', Rule::in(['upload', 'icon', 'emoji'])],
            'blocks.*.items.*.image.value' => 'required|string',

            'blocks.*.items.*.overrides' => 'nullable|array',

            // A container never contains a container: an item carrying any
            // of these is rejected outright, not silently accepted.
            'blocks.*.items.*.kind' => 'prohibited',
            'blocks.*.items.*.type' => 'prohibited',
            'blocks.*.items.*.items' => 'prohibited',
        ];

        $rules += StyleOverrideRules::rules('blocks.*.card.overrides', allowAlignAndSize: true);
        $rules += StyleOverrideRules::rules('blocks.*.items.*.overrides', allowAlignAndSize: true);

        foreach ((array) ($data['blocks'] ?? []) as $index => $block) {
            $kind = is_array($block) ? ($block['kind'] ?? null) : null;
            $type = is_array($block) ? ($block['type'] ?? null) : null;

            if ($kind === 'container') {
                $rules["blocks.$index.type"] = ['required', Rule::in(self::CONTAINER_TYPES)];
                $rules["blocks.$index.hidden"] = 'nullable|boolean';
                $rules["blocks.$index.config"] = 'required|array';
                $rules["blocks.$index.items"] = 'present|array';

                if ($type === 'carousel') {
                    $rules["blocks.$index.config.size"] = ['required', Rule::in(self::CAROUSEL_SIZES)];
                    $rules["blocks.$index.config.columns"] = 'prohibited';
                } elseif ($type === 'grid') {
                    $rules["blocks.$index.config.columns"] = ['required', Rule::in(self::GRID_COLUMNS)];
                    $rules["blocks.$index.config.size"] = 'prohibited';
                }
            } else {
                $rules["blocks.$index.type"] = ['required', Rule::in(self::V1_BLOCK_TYPES)];
                $rules["blocks.$index.hidden"] = 'required|boolean';
                $rules["blocks.$index.card"] = 'present|array';
                $rules["blocks.$index.layout"] = $type === 'link'
                    ? ['required', Rule::in(self::LINK_LAYOUTS)]
                    : 'required|string';
            }
        }

        return $rules;
    }
}
