<?php

use App\Models\Asset;
use App\Models\BlockClick;
use App\Models\Page;
use App\Models\PageView;
use App\Models\Profile;
use App\Models\Template;
use App\Models\User;

it('creates the full chain of related models from a single factory call', function () {
    $page = Page::factory()->create();

    expect($page->profile)->not->toBeNull();
    expect($page->profile->user)->not->toBeNull();
});

it('relates a user to its profile', function () {
    $profile = Profile::factory()->create();

    expect($profile->user)->toBeInstanceOf(User::class);
    expect($profile->user->profile->is($profile))->toBeTrue();
});

it('relates a profile to its page', function () {
    $page = Page::factory()->create();

    expect($page->profile)->toBeInstanceOf(Profile::class);
    expect($page->profile->page->is($page))->toBeTrue();
});

it('relates a profile to its assets', function () {
    $profile = Profile::factory()->create();
    $assets = Asset::factory()->count(2)->for($profile)->create();

    expect($profile->assets)->toHaveCount(2);
    expect($assets->first()->profile->is($profile))->toBeTrue();
});

it('relates a page to its page views and block clicks', function () {
    $page = Page::factory()->create();
    PageView::factory()->count(3)->for($page)->create();
    BlockClick::factory()->count(2)->for($page)->create();

    expect($page->pageViews)->toHaveCount(3);
    expect($page->blockClicks)->toHaveCount(2);
    expect($page->pageViews->first()->page->is($page))->toBeTrue();
    expect($page->blockClicks->first()->page->is($page))->toBeTrue();
});

it('has no relationship between templates and pages', function () {
    expect(method_exists(Template::class, 'pages'))->toBeFalse();
    expect(method_exists(Page::class, 'template'))->toBeFalse();
});

it('round-trips the content and theme jsonb casts on a page', function () {
    $content = [
        'header' => ['name' => 'Ana', 'bio' => 'Studio owner'],
        'socialIcons' => [['platform' => 'instagram', 'value' => 'ana.studio']],
        'blocks' => [],
    ];
    $theme = [
        'page' => ['header' => ['layout' => 'classic']],
        'blockDefaults' => ['tactile' => 'flat'],
        'fonts' => ['titleFont' => 'Inter', 'textFont' => 'Inter'],
        'palette' => [
            'background' => '#FFFFFF',
            'text' => '#111111',
            'surface' => '#F5F5F5',
            'onSurface' => '#111111',
            'accent' => '#6D28D9',
            'onAccent' => '#FFFFFF',
        ],
    ];

    $page = Page::factory()->create([
        'content' => $content,
        'theme' => $theme,
    ]);

    $fresh = Page::query()->findOrFail($page->id);

    // toEqual, not toBe: Postgres jsonb normalizes/reorders object keys, so
    // the round-tripped array is equal but not identically key-ordered.
    expect($fresh->content)->toEqual($content);
    expect($fresh->theme)->toEqual($theme);
});

it('round-trips the theme jsonb cast on a template', function () {
    $theme = ['palette' => ['background' => '#000000']];

    $template = Template::factory()->create(['theme' => $theme]);

    expect(Template::query()->findOrFail($template->id)->theme)->toEqual($theme);
});
