<?php

use App\Models\Tag;
use function Pest\Faker\fake;

use App\Models\Image;
use App\Models\MediaFile;

use Inertia\Testing\AssertableInertia as Assert;


test('認証時、media.indexが閲覧できる', function () {
    Storage::fake('private');
    login();

    $image = Image::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        "mediable_type" => Image::class,
        "mediable_id" => $image->id,
    ]);

    $this->get(route("media.index", ["mediaType"=>"all"]))
        ->assertInertia(fn (Assert $page) => $page
            ->component("Media/Index/Index")
            ->has("medias", 1)
            ->has("medias.0", fn (Assert $media ) => $media
                ->etc()
                ->where("id", $mediaFile->id)
                ->where("title", $mediaFile->title)
                ->has("mediable", fn (Assert $mediable) => $mediable
                    ->etc()
                    ->where("id", $image->id)
                    ->where("extension", $image->extension)
                )
            )
        );
});

test('非認証時、media.indexが閲覧できない', function () {
    Storage::fake('private');

    $image = Image::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        "mediable_type" => Image::class,
        "mediable_id" => $image->id,
    ]);

    $this->get(route("media.index", ["mediaType"=>"all"]))
        ->assertRedirect(route("login"));
});

test('media.indexでワード検索ができる', function () {
    Storage::fake('private');
    login();

    $image = Image::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        "mediable_type" => Image::class,
        "mediable_id" => $image->id,
        "title" => "初音ミク"
    ]);
    MediaFile::factory()->create(["title"=>"重音テト"]);

    $this->get(route("media.index", ["mediaType"=>"all", "word"=>$mediaFile->title]))
        ->assertInertia(fn (Assert $page) => $page
            ->component("Media/Index/Index")
            ->has("medias", 1)
            ->has("medias.0", fn (Assert $media ) => $media
                ->etc()
                ->where("id", $mediaFile->id)
                ->where("title", $mediaFile->title)
                ->has("mediable", fn (Assert $mediable) => $mediable
                    ->etc()
                    ->where("id", $image->id)
                    ->where("extension", $image->extension)
                )
            )
        );
});

test('media.indexでタグ検索ができる', function () {
    Storage::fake('private');
    login();

    $image = Image::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        "mediable_type" => Image::class,
        "mediable_id" => $image->id,
    ]);
    $tag = Tag::factory()->create();
    $mediaFile->tags()->attach([$tag->id]);

    MediaFile::factory()->create();

    $this->get(route("media.index", ["mediaType"=>"all", "tags"=>[$tag->name]]))
        ->assertInertia(fn (Assert $page) => $page
            ->component("Media/Index/Index")
            ->has("medias", 1)
            ->has("medias.0", fn (Assert $media ) => $media
                ->etc()
                ->where("id", $mediaFile->id)
                ->where("title", $mediaFile->title)
                ->has("mediable", fn (Assert $mediable) => $mediable
                    ->etc()
                    ->where("id", $image->id)
                    ->where("extension", $image->extension)
                )
            )
        );
});

test('media.indexでタグとワードのアンド検索ができる', function () {
    Storage::fake('private');
    login();

    $image = Image::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        "mediable_type" => Image::class,
        "mediable_id" => $image->id,
        "title" => "初音ミク"
    ]);
    $tag = Tag::factory()->create();
    $mediaFile->tags()->attach([$tag->id]);

    MediaFile::factory()->create(["title" => "初音ミク"]);

    $this->get(route("media.index", ["mediaType"=>"all", "tags"=>[$tag->name], "word"=>$mediaFile->title]))
        ->assertInertia(fn (Assert $page) => $page
            ->component("Media/Index/Index")
            ->has("medias", 1)
            ->has("medias.0", fn (Assert $media ) => $media
                ->etc()
                ->where("id", $mediaFile->id)
                ->where("title", $mediaFile->title)
                ->has("mediable", fn (Assert $mediable) => $mediable
                    ->etc()
                    ->where("id", $image->id)
                    ->where("extension", $image->extension)
                )
            )
        );
});

test('orientationで検索できる', function () {
    Storage::fake('private');
    login();

    $image = Image::factory()->create([
        "width" => 1000,
        "height" => 500,
    ]);
    $mediaFile = MediaFile::factory()->create([
        "mediable_type" => Image::class,
        "mediable_id" => $image->id,
    ]);
    $image2 = Image::factory()->create([
        "width" => 500,
        "height" => 1000,
    ]);
    $mediaFile2 = MediaFile::factory()->create([
        "mediable_type" => Image::class,
        "mediable_id" => $image2->id,
    ]);

    $this->get(route("media.index", ["mediaType"=>"all", "orientation"=>"horizon"]))
        ->assertInertia(fn (Assert $page) => $page
            ->component("Media/Index/Index")
            ->has("medias", 1)
            ->has("medias.0", fn (Assert $media ) => $media
                ->etc()
                ->where("id", $mediaFile->id)
                ->where("title", $mediaFile->title)
                ->has("mediable", fn (Assert $mediable) => $mediable
                    ->etc()
                    ->where("id", $image->id)
                    ->where("extension", $image->extension)
                )
                ->whereNot("id", $mediaFile2->id)
            )
        );
});
