<?php

use App\Models\Page;
use App\Models\Profile;
use App\Models\Template;
use Database\Seeders\TemplateSeeder;

beforeEach(function () {
    $this->seed(TemplateSeeder::class);
    $this->profile = Profile::factory()->create(['slug' => config('qlinqs.dev_profile_slug')]);
    $this->page = Page::factory()->for($this->profile)->create();
});

it('lists templates with the expected shape, ordered by name, via GET /api/templates', function () {
    $response = $this->getJson('/api/templates');

    $response->assertOk();
    $data = $response->json();

    expect($data)->not->toBeEmpty();

    foreach ($data as $template) {
        expect($template)->toHaveKey('id');
        expect($template)->toHaveKey('name');
        expect($template)->toHaveKey('preview');
        expect($template)->toHaveKey('theme');
    }

    $names = collect($data)->pluck('name')->all();
    $sorted = collect($names)->sort()->values()->all();
    expect($names)->toBe($sorted);
});

it('applies a seeded template to the page, changing only its theme', function () {
    $originalContent = $this->page->content;
    $template = Template::first();

    $response = $this->postJson("/api/page/apply-template/{$template->id}");

    $response->assertOk();
    $this->page->refresh();
    expect($this->page->theme)->toEqual($template->theme);
    expect($this->page->content)->toEqual($originalContent);
});

it('does not change the page after a seeded template is edited afterwards', function () {
    $template = Template::first();

    $this->postJson("/api/page/apply-template/{$template->id}")->assertOk();
    $this->page->refresh();
    $appliedTheme = $this->page->theme;

    // Direct attribute assignment, not update(): template CRUD is out of
    // scope — this just simulates "the template changed later".
    $template->theme = [
        'page' => [
            'header' => ['layout' => 'classic'],
            'background' => ['type' => 'none'],
            'profilePicture' => [],
        ],
        'blockDefaults' => [],
        'fonts' => [],
        'palette' => [
            'background' => '#000000',
            'text' => '#ffffff',
            'surface' => '#111111',
            'onSurface' => '#ffffff',
            'accent' => '#ffffff',
            'onAccent' => '#000000',
        ],
    ];
    $template->save();

    $this->page->refresh();
    expect($this->page->theme)->toEqual($appliedTheme);
});
