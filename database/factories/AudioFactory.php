<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Audio>
 */
class AudioFactory extends Factory
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
            "extension" => "mp3",
            "duration" => random_int(1,1000),
            "raw_image_path" => "extras/audios/$fileName.mp3/raw.webp",
            "preview_audio_path" => "extras/audios/$fileName.mp3/prev.mp3",
        ];
    }
}
