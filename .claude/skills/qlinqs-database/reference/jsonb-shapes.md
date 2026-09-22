# `pages.content` / `pages.theme` / `templates.theme` — enforced shape

Postgres guarantees these three columns hold **valid JSON**. It guarantees
nothing about their internal shape. The shape is owned entirely by
`app/Validation/PageContentRules.php`, `PageThemeRules.php`, and the shared
`StyleOverrideRules.php` — wrapped by `ValidatePageContentRequest` /
`ValidatePageThemeRequest` and used by `PUT /api/page/content` /
`PUT /api/page/theme`. `templates.theme` uses the **same** `PageThemeRules`
— there is no separate template validator.

This file is the rules, verbatim from code, current as of the container-blocks
and link-layout-reorg changes. For the HTTP-facing version of this same
content (with request/response examples), see `API-MAPPING.md` at the project root
§2 — keep both in sync if you change a rule.

## `pages.content`

```
{ header, socialIcons, blocks }
```

| Field path | Rule |
|---|---|
| `header` | `required\|array` |
| `header.name` | `required\|string` |
| `header.bio` | `nullable\|string` |
| `socialIcons` | `present\|array` — key must exist, **can be empty** |
| `socialIcons.*.platform` | `required\|string` (no enum — free list) |
| `socialIcons.*.value` | `required\|string` |
| `blocks` | `present\|array` — key must exist, **can be empty** |
| `blocks.*.id` | `required\|string` |
| `blocks.*.kind` | `required`, enum `["atomic","container"]` |

### Atomic blocks (`kind: "atomic"`)

| Field path | Rule |
|---|---|
| `blocks.*.type` | `required`, enum `["link","whatsapp","maps","text","heading"]` |
| `blocks.*.layout` | `required`. Enum `["button","thumbnail","background","featured"]` **only when `type === "link"`**; any non-empty string for every other type. `"background"` = image fills the whole button as a background, title overlaid (our own layout, not in the conceptual data doc). |
| `blocks.*.hidden` | `required\|boolean` |
| `blocks.*.card` | `present\|array` — key must exist, **can be an empty object** |
| `blocks.*.card.title` / `.description` / `.buttonText` / `.label` | `nullable\|string` |
| `blocks.*.card.link` | `nullable\|array` |
| `blocks.*.card.link.kind` | required **only if `card.link` is present**, enum `["url","email"]` |
| `blocks.*.card.link.href` | required **only if `card.link` is present**, string |
| `blocks.*.card.image` | `nullable\|array` — optional on an atomic card |
| `blocks.*.card.image.source` | required **only if `card.image` is present**, enum `["upload","icon","emoji"]` |
| `blocks.*.card.image.value` | required **only if `card.image` is present**, string |
| `blocks.*.card.overrides` | `nullable\|array` — see **Style overrides** below, `align`/`size`/`imagePosition` **allowed** |

### Container blocks (`kind: "container"`)

| Field path | Rule |
|---|---|
| `blocks.*.type` | `required`, enum `["carousel","grid"]` |
| `blocks.*.layout` | **not validated at all** — containers have no layout concept |
| `blocks.*.hidden` | `nullable\|boolean` (optional, unlike atomic) |
| `blocks.*.config` | `required\|array` |
| `blocks.*.config.size` | **`type === "carousel"` only**: `required`, enum `["large","small"]`; `prohibited` on a grid |
| `blocks.*.config.columns` | **`type === "grid"` only**: `required`, enum `[2,3]`; `prohibited` on a carousel |
| `blocks.*.items` | `present\|array` — key must exist, **can be empty** (a container the editor hasn't filled in yet is still savable) |
| `blocks.*.items.*.title` / `.description` / `.buttonText` / `.label` | `nullable\|string` — an item is a card, same fields as `card` minus `layout` |
| `blocks.*.items.*.link`, `.link.kind`, `.link.href` | same as `card.link` |
| `blocks.*.items.*.image` | **`required\|array`** — unlike an atomic card, image is mandatory on every container item |
| `blocks.*.items.*.image.source` | **`required`** (not conditional), enum `["upload","icon","emoji"]` |
| `blocks.*.items.*.image.value` | **`required`** (not conditional), string |
| `blocks.*.items.*.overrides` | `nullable\|array` — same shape as `card.overrides` |
| `blocks.*.items.*.kind` / `.type` / `.items` | **`prohibited`** — an item carrying any of these looks like a nested container and is rejected outright (containers never contain containers) |

## `pages.theme` / `templates.theme`

```
{ page, blockDefaults, fonts, palette }
```

| Field path | Rule |
|---|---|
| `page` | `required\|array` |
| `page.header` | `required\|array` |
| `page.header.layout` | `required`, enum `["classic","business"]` |
| `page.background` | `required\|array` |
| `page.background.type` | `required`, enum `["none","solid","gradient"]` — **`type` is our own discriminator key name**; the conceptual doc doesn't name one |
| `page.profilePicture` | `present\|array` — key must exist, **can be an empty object**, no sub-fields validated |
| `blockDefaults` | `present\|array` — key must exist, **can be an empty object** — see **Style overrides** below, `align`/`size`/`imagePosition` **prohibited** |
| `fonts` | `present\|array` — key must exist, **can be an empty object** |
| `fonts.titleFont` / `.textFont` | `nullable\|string` |
| `palette` | `required\|array` |
| `palette.background` / `.text` / `.surface` / `.onSurface` / `.accent` / `.onAccent` | all **`required\|string`** — no exceptions, no hex-format check |

## Style overrides (shared shape: `card.overrides`, `items.*.overrides`, `blockDefaults`)

| Field | Rule |
|---|---|
| `tactile` | `nullable`, enum `["flat","concave","convex","inset","glass","none"]` |
| `color` / `textColor` / `borderColor` | `nullable\|string` (no hex format enforced) |
| `corner` / `border` / `shadow` / `spacing` | `nullable\|integer`, `0`–`100` |
| `shadowStyle` | `nullable`, enum `["soft","solid"]` |
| `align` | card/item overrides: `nullable`, enum `["left","center","right"]`. `blockDefaults`: **`prohibited`**. |
| `size` | card/item overrides: `nullable`, enum `["large","small"]`. `blockDefaults`: **`prohibited`**. |
| `imagePosition` | card/item overrides: `nullable`, enum `["left","right"]` (used by the link block's `thumbnail` layout). `blockDefaults`: **`prohibited`**. Our own addition, not in the conceptual doc. |

`align`/`size`/`imagePosition` are block-only because they never have a
theme-level default to inherit — a container the validator wouldn't catch
otherwise, hence the explicit `prohibited` rather than just leaving the rule
undefined for `blockDefaults`.

**Not enforced by the validator, deliberately:**
- Tactile-vs-border/shadow mutual exclusivity (conceptual doc §5.3) — a
  render/UI concern only; sending both together saves successfully.
- Color format (hex vs. palette-role reference) — open item in the
  conceptual doc.
- `layout` values for non-`link` atomic types — no enum defined for
  `whatsapp`/`maps`/`text`/`heading`; any non-empty string passes.

## Minimal valid examples (validated live via `php artisan tinker` +
`Validator::make(...)->passes()`)

```json
// content
{
  "header": { "name": "Ana" },
  "socialIcons": [],
  "blocks": []
}
```

```json
// theme
{
  "page": {
    "header": { "layout": "classic" },
    "background": { "type": "solid" },
    "profilePicture": {}
  },
  "blockDefaults": {},
  "fonts": {},
  "palette": {
    "background": "#FFFFFF",
    "text": "#111111",
    "surface": "#F5F5F5",
    "onSurface": "#111111",
    "accent": "#6D28D9",
    "onAccent": "#FFFFFF"
  }
}
```

```json
// content — one valid carousel container block
{
  "id": "blk_carousel",
  "kind": "container",
  "type": "carousel",
  "config": { "size": "large" },
  "items": [
    { "title": "Slide 1", "image": { "source": "upload", "value": "asset-1" } }
  ]
}
```

## Persistence — the part that surprises people

`PageController::updateContent`/`updateTheme` do **not** save
`$request->validated()`. They save `Arr::only($request->all(),
$topLevelKeys)` — `['header','socialIcons','blocks']` for content,
`['page','blockDefaults','fonts','palette']` for theme. Practical effect:

- Any nested field under those top-level keys is saved **exactly as sent**,
  even where the validator has no explicit rule for it — only actually
  invalid values (wrong type, bad enum, out of range, `prohibited`) get
  rejected. Unrecognized-but-harmless nested keys are kept, not dropped.
- Any top-level key outside that fixed list is silently dropped and never
  reaches the database.
- This was a deliberate fix after a QA round caught the opposite bug
  (`$request->validated()` was silently stripping allowed-but-unruled
  nested fields). Don't revert it to `validated()`.
