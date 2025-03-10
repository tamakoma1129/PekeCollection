<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MediaFile>
 */
class MediaFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "title" => "image",
            "base_name" => "image.png",
            "path" => $this->faker->imageUrl(),
            "data_size" => 1024,
            "mediable_type" => Image::class,
            "mediable_id" => Image::factory(),
            "preview_image_path" => null,
        ];
    }
}
