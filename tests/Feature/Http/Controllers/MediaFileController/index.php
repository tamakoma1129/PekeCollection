<?php

use App\Models\Audio;
use App\Models\Image;
use App\Models\Manga;
use App\Models\MangaPage;
use App\Models\MediaFile;
use App\Models\Video;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('private');
    login();
});

test('media-all一覧が閲覧できる', function () {
    $image = MediaFile::factory()->for(Image::factory()->create(), "mediable")->create();
    $video = MediaFile::factory()->for(Video::factory()->create(), "mediable")->create();
    $audio = MediaFile::factory()->for(Audio::factory()->create(), "mediable")->create();
    $manga = MediaFile::factory()->for(Manga::factory()->create(), "mediable")->create();
    $mangaPage = MangaPage::factory()->for($manga->mediable, "manga")->create();

    $response = $this->get(route('media.index', ['mediaType' => "all"]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Media/Index/Index')
        ->has('medias', 4)
        ->has('medias.0', fn (Assert $page) => $page
            ->where('id', $image->id)
            ->etc()
        )
        ->has('medias.1', fn (Assert $page) => $page
            ->where('id', $video->id)
            ->etc()
        )
        ->has('medias.2', fn (Assert $page) => $page
            ->where('id', $audio->id)
            ->etc()
        )
        ->has('medias.3', fn (Assert $page) => $page
            ->where('id', $manga->id)
            ->has("mediable", fn (Assert $page) => $page
                ->has("pages.0", fn (Assert $page) => $page
                    ->where('id', $mangaPage->id)
                    ->etc()
                )
                ->etc()
            )
            ->etc()
        )
    );
});

test('ゲストはmedia一覧が閲覧できない', function () {
    auth()->logout();

    $image = MediaFile::factory()->for(Image::factory()->create(), "mediable")->create();
    $video = MediaFile::factory()->for(Video::factory()->create(), "mediable")->create();
    $audio = MediaFile::factory()->for(Audio::factory()->create(), "mediable")->create();
    $manga = MediaFile::factory()->for(Manga::factory()->create(), "mediable")->create();
    $mangaPage = MangaPage::factory()->for($manga->mediable, "manga")->create();

    $response = $this->get(route('media.index', ['mediaType' => "all"]));

    $response->assertRedirect((route("login")));
});
