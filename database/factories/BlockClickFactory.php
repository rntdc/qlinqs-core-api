<?php

namespace Database\Factories;

use App\Models\BlockClick;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlockClick>
 */
class BlockClickFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'block_id' => (string) Str::uuid(),
        ];
    }
}
