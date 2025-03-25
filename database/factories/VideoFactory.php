<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = $this->faker->unique()->word();
        return [
            "extension" => "mp4",
            "duration" => random_int(1,1000),
            "width" => random_int(10,2000),
            "height" => random_int(10,2000),
            "raw_image_path" => "extras/videos/$fileName.mp4/raw.webp",
            "preview_video_path" => "extras/videos/$fileName.mp4/anime_prev.webp",
        ];
    }
}
