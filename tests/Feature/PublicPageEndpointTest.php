<?php

use App\Models\Page;
use App\Models\Profile;

it('returns the page content and theme via GET /api/p/{slug}', function () {
    $profile = Profile::factory()->create(['slug' => 'aurora-clinic']);
    $page = Page::factory()->for($profile)->create();

    $response = $this->getJson('/api/p/aurora-clinic');

    $response->assertOk();
    $response->assertJson([
        'slug' => 'aurora-clinic',
        'content' => $page->content,
        'theme' => $page->theme,
    ]);
});

it('resolves the slug case-insensitively', function () {
    $profile = Profile::factory()->create(['slug' => 'aurora-clinic']);
    Page::factory()->for($profile)->create();

    $response = $this->getJson('/api/p/Aurora-Clinic');

    $response->assertOk();
    expect($response->json('slug'))->toBe('aurora-clinic');
});

it('returns 404 for an unknown slug', function () {
    $response = $this->getJson('/api/p/does-not-exist');

    $response->assertStatus(404);
});

it('returns 404 when the profile has no page', function () {
    Profile::factory()->create(['slug' => 'no-page-yet']);

    $response = $this->getJson('/api/p/no-page-yet');

    $response->assertStatus(404);
});

it('does not expose internal ids or user data in the response', function () {
    $profile = Profile::factory()->create(['slug' => 'private-check']);
    Page::factory()->for($profile)->create();

    $response = $this->getJson('/api/p/private-check');

    $response->assertOk();
    $body = $response->json();

    expect(collect($body)->keys()->all())->toBe(['slug', 'content', 'theme']);
    expect($body)->not->toHaveKey('id');
    expect($body)->not->toHaveKey('user_id');
    expect($body)->not->toHaveKey('email');
});
