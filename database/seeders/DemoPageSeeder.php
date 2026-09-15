<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Profile;
use App\Models\User;
use Database\Factories\PageFactory;
use Illuminate\Database\Seeder;

/**
 * Creates a single demo user/profile/page so the page content/theme
 * endpoints have something to act on before real auth exists. Idempotent —
 * safe to re-run. Doesn't seed templates; that's a later task.
 */
class DemoPageSeeder extends Seeder
{
    public function run(): void
    {
        $slug = config('qlinqs.dev_profile_slug', 'teste');

        // Tied to the slug, not hardcoded: profiles.user_id is unique, so a
        // fixed email would collide with an existing profile if the slug
        // ever changes without that older row being removed.
        $user = User::firstOrCreate(
            ['email' => "{$slug}@qlinqs.test"],
            ['name' => 'Demo User', 'password' => 'password'],
        );

        $profile = Profile::firstOrCreate(
            ['slug' => $slug],
            ['user_id' => $user->id],
        );

        Page::firstOrCreate(
            ['profile_id' => $profile->id],
            [
                'content' => PageFactory::new()->definition()['content'],
                'theme' => PageFactory::defaultTheme(),
            ],
        );
    }
}
