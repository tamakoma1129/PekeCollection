<?php

namespace Database\Factories;

use App\Models\MediaFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "extension" => "jpg",
            "width" => rand(10, 1000),
            "height" => rand(10, 1000),
        ];
    }
}
