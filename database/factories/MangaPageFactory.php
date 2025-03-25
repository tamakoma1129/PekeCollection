<?php

namespace Database\Factories;

use App\Models\Manga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MangaPage>
 */
class MangaPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "manga_id" => Manga::factory(),
            "page_number" => 1,
            "file_name" => "1.png",
            "path" => "uploads/mangas/manga/1.png",
            "lite_path" => "extras/mangas/manga/1.webp",
            "file_extension" => "png",
            "width" => random_int(10,2000),
            "height" => random_int(10,2000),
            "file_size" => random_int(10,10000000),
        ];
    }
}
