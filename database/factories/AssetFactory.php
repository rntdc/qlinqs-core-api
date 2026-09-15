<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
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
            'storage_path' => 'assets/'.fake()->uuid().'.png',
            'mime_type' => 'image/png',
            'size_bytes' => fake()->numberBetween(1_000, 5_000_000),
            'width' => fake()->numberBetween(100, 2000),
            'height' => fake()->numberBetween(100, 2000),
        ];
    }
}
