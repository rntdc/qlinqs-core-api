# Eloquent layer reference

All in `app/Models/`. Verified by reading current source (2026-09-21).

## `User` (`app/Models/User.php`)

- Extends `Illuminate\Foundation\Auth\User` (Sanctum-style Authenticatable).
- `HasUuids` — `id` is a uuid, app-generated on create.
- `#[Fillable(['name', 'email', 'password'])]`, `#[Hidden(['password',
  'remember_token'])]` (PHP attribute-based, not the classic `$fillable`
  property — matches this codebase's convention).
- Casts: `email_verified_at` → `datetime`, `password` → `hashed`
  (auto-hashes on assignment).
- Standard Eloquent timestamps (`created_at`/`updated_at` both managed).
- Relationship: `profile(): HasOne` → `Profile`.
- Factory: `UserFactory` (default Laravel scaffolding — name/email/hashed
  password via `fake()`).

## `Profile` (`app/Models/Profile.php`)

- `HasUuids`. No `$fillable`/`#[Fillable]` declared — nothing in the API
  mass-assigns a `Profile` today (factories bypass mass-assignment guarding
  entirely via `Model::unguarded()`, so this doesn't block tests/seeding).
- No custom casts. `slug` stays a plain string at this layer — citext is a
  DB-only concern, not reflected in a cast.
- Standard timestamps.
- Relationships: `user(): BelongsTo` → `User`; `page(): HasOne` → `Page`;
  `assets(): HasMany` → `Asset`.
- Factory: `ProfileFactory` — `user_id` via `User::factory()`, `slug` via
  `fake()->unique()->slug(2)`.

## `Page` (`app/Models/Page.php`)

- `HasUuids`. `#[Fillable(['content', 'theme'])]` — the only two fields
  ever mass-assigned (by `PageController`); `profile_id` is deliberately
  not fillable.
- Casts: `content` → `array`, `theme` → `array` (PHP `array` cast on a
  jsonb column — Eloquent handles the JSON encode/decode transparently).
- Standard timestamps; `updated_at` bumps automatically on every
  `->update()`.
- Relationships: `profile(): BelongsTo` → `Profile`; `pageViews(): HasMany`
  → `PageView`; `blockClicks(): HasMany` → `BlockClick`.
- Factory: `PageFactory` — produces a minimal-but-valid `content`/`theme`
  pair that passes `PageContentRules`/`PageThemeRules` (see
  `jsonb-shapes.md`). `PageFactory::defaultTheme()` is a public static
  method reused by `TemplateFactory` so both factories emit the same theme
  shape.

## `Template` (`app/Models/Template.php`)

- `HasUuids`. `#[Fillable(['name', 'preview', 'theme'])]`.
- `const UPDATED_AT = null` — the table has no `updated_at` column at all,
  not just an unused one; this constant tells Eloquent not to try writing
  it.
- Cast: `theme` → `array`.
- **No relationships, on purpose** (a code comment says so explicitly):
  applying a template copies its `theme` into `pages.theme`; it doesn't
  link to it. Adding a `pages()` relationship here would misrepresent the
  actual data flow.
- Factory: `TemplateFactory` — `theme` via `PageFactory::defaultTheme()`.
- Seeded by `database/seeders/TemplateSeeder.php` (`Template::updateOrCreate(['name' => …], […])`
  — idempotent, 4 starter templates: Clínica Aurora, Bold Creator, Midnight,
  Soft Pastel).

## `Asset` (`app/Models/Asset.php`)

- `HasUuids`. No `$fillable` declared (no create/update endpoint exists
  yet to need it).
- `const UPDATED_AT = null` — same reasoning as `Template`, no such column.
- Relationship: `profile(): BelongsTo` → `Profile`.
- Factory: `AssetFactory` — fake `storage_path`/`mime_type`/`size_bytes`/
  `width`/`height`.

## `PageView` (`app/Models/PageView.php`)

- **No `HasUuids`** — PK is the DB's bigint identity column, not app-set.
- `public $timestamps = false` — the table has neither `created_at` nor
  `updated_at`; `viewed_at` is a DB-side `CURRENT_TIMESTAMP` default that
  Eloquent doesn't manage. (After `->create()`, the in-memory attribute may
  read blank until you `->refresh()` — the DB row itself is correct
  immediately; this is normal Eloquent behavior for a DB-generated default
  Eloquent didn't insert itself.)
- Relationship: `page(): BelongsTo` → `Page`.
- Factory: `PageViewFactory` — just `page_id` via `Page::factory()`;
  `viewed_at` is left to the DB default.

## `BlockClick` (`app/Models/BlockClick.php`)

- Same shape as `PageView`: no `HasUuids`, `public $timestamps = false`.
- Relationship: `page(): BelongsTo` → `Page`.
- Factory: `BlockClickFactory` — `page_id` via `Page::factory()`,
  `block_id` via `Str::uuid()` (a logical id, no FK enforced against
  anything).

## Cross-model notes

- Every uuid-PK model (`User`, `Profile`, `Page`, `Template`, `Asset`) uses
  Laravel's `Illuminate\Database\Eloquent\Concerns\HasUuids` trait — the ID
  is generated app-side before insert, there's no DB-side `gen_random_uuid()`
  default. Don't add one; it would fight the trait.
- Factories never need explicit `$fillable` bypass: `Illuminate\Database\Eloquent\Factories\Factory`
  wraps creation in `Model::unguarded()`, so a model with no `$fillable`/
  `#[Fillable]` (like `Profile`, `Asset`, `PageView`, `BlockClick`) can
  still be created via its factory in tests/seeders without a
  `MassAssignmentException`. Only real controller-driven mass assignment
  (`Page::update([...])`, `Template::updateOrCreate([...])`) needs the
  attribute declared — which is why only `Page` and `Template` have one.
