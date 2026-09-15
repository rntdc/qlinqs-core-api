<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

/**
 * Starter templates for the template gallery (qlinqs-estrutura-de-dados.md
 * §7.5) — curated, visually distinct themes a user can apply with one
 * click. Idempotent: updateOrCreate by name, safe to re-run.
 */
class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            Template::updateOrCreate(
                ['name' => $template['name']],
                [
                    'preview' => $template['preview'],
                    'theme' => $template['theme'],
                ],
            );
        }
    }

    /**
     * @return array<int, array{name: string, preview: string, theme: array<string, mixed>}>
     */
    protected function templates(): array
    {
        return [
            [
                'name' => 'Clínica Aurora',
                'preview' => 'https://placehold.co/480x854?text=Clinica+Aurora',
                'theme' => [
                    'page' => [
                        'header' => ['layout' => 'business'],
                        'background' => ['type' => 'solid', 'color' => '#F7FAFC'],
                        'profilePicture' => ['shape' => 'circle'],
                    ],
                    'blockDefaults' => [
                        'tactile' => 'flat',
                        'corner' => 12,
                        'spacing' => 40,
                    ],
                    'fonts' => [
                        'titleFont' => 'Inter',
                        'textFont' => 'Inter',
                    ],
                    'palette' => [
                        'background' => '#F7FAFC',
                        'text' => '#1A202C',
                        'surface' => '#FFFFFF',
                        'onSurface' => '#1A202C',
                        'accent' => '#2B6CB0',
                        'onAccent' => '#FFFFFF',
                    ],
                ],
            ],
            [
                'name' => 'Bold Creator',
                'preview' => 'https://placehold.co/480x854?text=Bold+Creator',
                'theme' => [
                    'page' => [
                        'header' => ['layout' => 'classic'],
                        'background' => ['type' => 'gradient', 'from' => '#FF6B6B', 'to' => '#FFD93D'],
                        'profilePicture' => ['size' => 'large'],
                    ],
                    'blockDefaults' => [
                        'tactile' => 'convex',
                        'corner' => 24,
                        'spacing' => 32,
                    ],
                    'fonts' => [
                        'titleFont' => 'Poppins',
                        'textFont' => 'Inter',
                    ],
                    'palette' => [
                        'background' => '#FFF8F0',
                        'text' => '#241623',
                        'surface' => '#FFFFFF',
                        'onSurface' => '#241623',
                        'accent' => '#FF3D81',
                        'onAccent' => '#FFFFFF',
                    ],
                ],
            ],
            [
                'name' => 'Midnight',
                'preview' => 'https://placehold.co/480x854?text=Midnight',
                'theme' => [
                    'page' => [
                        'header' => ['layout' => 'classic'],
                        'background' => ['type' => 'solid', 'color' => '#0B0B12'],
                        'profilePicture' => ['shadow' => 40],
                    ],
                    'blockDefaults' => [
                        'tactile' => 'glass',
                        'corner' => 20,
                        'shadow' => 30,
                        'shadowStyle' => 'soft',
                        'spacing' => 36,
                    ],
                    'fonts' => [
                        'titleFont' => 'Space Grotesk',
                        'textFont' => 'Inter',
                    ],
                    'palette' => [
                        'background' => '#0B0B12',
                        'text' => '#F5F5F7',
                        'surface' => '#17171F',
                        'onSurface' => '#F5F5F7',
                        'accent' => '#7C5CFF',
                        'onAccent' => '#FFFFFF',
                    ],
                ],
            ],
            [
                'name' => 'Soft Pastel',
                'preview' => 'https://placehold.co/480x854?text=Soft+Pastel',
                'theme' => [
                    'page' => [
                        'header' => ['layout' => 'business'],
                        'background' => ['type' => 'gradient', 'from' => '#FDEFF9', 'to' => '#E9E4FF'],
                        'profilePicture' => [],
                    ],
                    'blockDefaults' => [
                        'tactile' => 'concave',
                        'corner' => 28,
                        'spacing' => 44,
                    ],
                    'fonts' => [
                        'titleFont' => 'Quicksand',
                        'textFont' => 'Nunito',
                    ],
                    'palette' => [
                        'background' => '#FFF6FB',
                        'text' => '#4A3B4C',
                        'surface' => '#FFFFFF',
                        'onSurface' => '#4A3B4C',
                        'accent' => '#C084FC',
                        'onAccent' => '#FFFFFF',
                    ],
                ],
            ],
        ];
    }
}
