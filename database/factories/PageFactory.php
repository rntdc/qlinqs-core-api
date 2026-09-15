<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 *
 * The content/theme shape here is a minimal, provisional approximation of
 * what `qlinqs-estrutura-de-dados.md` is expected to define in full — that
 * document doesn't exist in the repo yet. Update this factory once it lands.
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'content' => [
                'header' => [
                    'name' => fake()->name(),
                    'bio' => fake()->sentence(),
                ],
                'socialIcons' => [
                    ['platform' => 'instagram', 'value' => fake()->userName()],
                ],
                'blocks' => [
                    [
                        'id' => (string) Str::uuid(),
                        'kind' => 'atomic',
                        'type' => 'link',
                        'layout' => 'button',
                        'hidden' => false,
                        'card' => [
                            'title' => fake()->words(3, true),
                            'link' => [
                                'kind' => 'url',
                                'href' => fake()->url(),
                            ],
                        ],
                    ],
                ],
            ],
            'theme' => static::defaultTheme(),
        ];
    }

    /**
     * A minimal, provisional theme shape shared with TemplateFactory.
     *
     * @return array<string, mixed>
     */
    public static function defaultTheme(): array
    {
        return [
            'page' => [
                'header' => ['layout' => 'classic'],
                'background' => ['type' => 'solid', 'color' => '#FFFFFF'],
                'profilePicture' => [],
            ],
            'blockDefaults' => [
                'tactile' => 'flat',
                'corner' => 16,
            ],
            'fonts' => [
                'titleFont' => 'Inter',
                'textFont' => 'Inter',
            ],
            'palette' => [
                'background' => '#FFFFFF',
                'text' => '#111111',
                'surface' => '#F5F5F5',
                'onSurface' => '#111111',
                'accent' => '#6D28D9',
                'onAccent' => '#FFFFFF',
            ],
        ];
    }
}
