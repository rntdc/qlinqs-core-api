<?php

use App\Models\Page;
use App\Models\Profile;
use App\Models\Template;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->profile = Profile::factory()->create(['slug' => config('qlinqs.dev_profile_slug')]);
    $this->page = Page::factory()->for($this->profile)->create();
});

it('updates only content via PUT /api/page/content', function () {
    $originalTheme = $this->page->theme;
    $payload = validPageContent();
    $payload['header']['name'] = 'Updated Name';

    $response = $this->putJson('/api/page/content', $payload);

    $response->assertOk();
    $this->page->refresh();
    expect($this->page->content['header']['name'])->toBe('Updated Name');
    expect($this->page->theme)->toEqual($originalTheme);
});

it('updates only theme via PUT /api/page/theme', function () {
    $originalContent = $this->page->content;
    $payload = validPageTheme();
    $payload['page']['header']['layout'] = 'business';

    $response = $this->putJson('/api/page/theme', $payload);

    $response->assertOk();
    $this->page->refresh();
    expect($this->page->theme['page']['header']['layout'])->toBe('business');
    expect($this->page->content)->toEqual($originalContent);
});

it('rejects an invalid content payload with 422 and leaves the DB unchanged', function () {
    $originalContent = $this->page->content;
    $payload = validPageContent();
    unset($payload['header']['name']);

    $response = $this->putJson('/api/page/content', $payload);

    $response->assertStatus(422);
    $this->page->refresh();
    expect($this->page->content)->toEqual($originalContent);
});

it('rejects an invalid theme payload with 422 and leaves the DB unchanged', function () {
    $originalTheme = $this->page->theme;
    $payload = validPageTheme();
    unset($payload['palette']['accent']);

    $response = $this->putJson('/api/page/theme', $payload);

    $response->assertStatus(422);
    $this->page->refresh();
    expect($this->page->theme)->toEqual($originalTheme);
});

it('applies a template theme exactly and leaves content unchanged', function () {
    $originalContent = $this->page->content;
    $template = Template::factory()->create();

    $response = $this->postJson("/api/page/apply-template/{$template->id}");

    $response->assertOk();
    $this->page->refresh();
    expect($this->page->theme)->toEqual($template->theme);
    expect($this->page->content)->toEqual($originalContent);
});

it('does not change the applied page when the template is edited afterwards', function () {
    $template = Template::factory()->create();

    $this->postJson("/api/page/apply-template/{$template->id}")->assertOk();
    $this->page->refresh();
    $appliedTheme = $this->page->theme;

    // Direct attribute assignment, not update(): Template has no fillable
    // fields of its own (template CRUD is out of this task's scope) — this
    // just simulates "the template changed later" from outside the app.
    $template->theme = [
        'page' => [
            'header' => ['layout' => 'business'],
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

it('returns 404 for an unknown template uuid', function () {
    $response = $this->postJson('/api/page/apply-template/'.(string) Str::uuid());

    $response->assertStatus(404);
});

it('returns 404 with a clear message when no dev profile page exists', function () {
    Profile::query()->delete();

    $response = $this->putJson('/api/page/content', validPageContent());

    $response->assertStatus(404);
    expect($response->json('message'))->toContain(config('qlinqs.dev_profile_slug'));
});

it('persists nested theme fields the validator allows but does not enumerate, and drops an unknown top-level key', function () {
    $payload = validPageTheme();
    $payload['page']['header']['sheetColor'] = '#fff';
    $payload['page']['header']['fade'] = true;
    $payload['page']['background'] = ['type' => 'gradient', 'from' => '#000', 'to' => '#fff'];
    $payload['page']['profilePicture'] = ['size' => 'large', 'shadow' => 20];
    $payload['hack'] = 1;

    $response = $this->putJson('/api/page/theme', $payload);

    $response->assertOk();
    $this->page->refresh();
    expect($this->page->theme['page']['header']['sheetColor'])->toBe('#fff');
    expect($this->page->theme['page']['header']['fade'])->toBeTrue();
    expect($this->page->theme['page']['background'])->toEqual(['type' => 'gradient', 'from' => '#000', 'to' => '#fff']);
    expect($this->page->theme['page']['profilePicture'])->toEqual(['size' => 'large', 'shadow' => 20]);
    expect($this->page->theme)->not->toHaveKey('hack');
});

it('persists nested content fields the validator allows but does not enumerate, and drops an unknown top-level key', function () {
    $payload = validPageContent();
    $payload['header']['sheetColor'] = '#fff';
    $payload['blocks'][0]['card']['label'] = 'Probe Label';
    $payload['blocks'][0]['card']['image'] = ['source' => 'emoji', 'value' => '🔥'];
    $payload['blocks'][0]['card']['overrides'] = ['tactile' => 'glass', 'corner' => 22];
    $payload['hack'] = 1;

    $response = $this->putJson('/api/page/content', $payload);

    $response->assertOk();
    $this->page->refresh();
    expect($this->page->content['header']['sheetColor'])->toBe('#fff');
    expect($this->page->content['blocks'][0]['card']['label'])->toBe('Probe Label');
    expect($this->page->content['blocks'][0]['card']['image'])->toEqual(['source' => 'emoji', 'value' => '🔥']);
    expect($this->page->content['blocks'][0]['card']['overrides'])->toEqual(['tactile' => 'glass', 'corner' => 22]);
    expect($this->page->content)->not->toHaveKey('hack');
});
