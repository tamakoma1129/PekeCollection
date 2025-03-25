<?php

use App\Models\Audio;
use App\Models\Image;
use App\Models\Manga;
use App\Models\MangaPage;
use App\Models\MediaFile;
use App\Models\Tag;
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
            ->where('mediable_type', $image->mediable_type)
            ->etc()
        )
        ->has('medias.1', fn (Assert $page) => $page
            ->where('mediable_type', $video->mediable_type)
            ->etc()
        )
        ->has('medias.2', fn (Assert $page) => $page
            ->where('mediable_type', $audio->mediable_type)
            ->etc()
        )
        ->has('medias.3', fn (Assert $page) => $page
            ->where('mediable_type', $manga->mediable_type)
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

test('media一覧でメディア種別絞り込みできる', function (
    string $mediaType,
    string $mediable_type
) {
    $image = MediaFile::factory()->for(Image::factory()->create(), "mediable")->create();
    $video = MediaFile::factory()->for(Video::factory()->create(), "mediable")->create();
    $audio = MediaFile::factory()->for(Audio::factory()->create(), "mediable")->create();
    $manga = MediaFile::factory()->for(Manga::factory()->create(), "mediable")->create();
    $mangaPage = MangaPage::factory()->for($manga->mediable, "manga")->create();

    $response = $this->get(route('media.index', ['mediaType' => $mediaType]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Media/Index/Index')
        ->has('medias', 1)
        ->has('medias.0', fn (Assert $page) => $page
            ->where('mediable_type', $mediable_type)
            ->etc()
        )
    );
})
    ->with([
        ["image", Image::class],
        ["video", Video::class],
        ["audio", Audio::class],
        ["manga", Manga::class],
    ]);

test('media一覧でワード絞り込みができる', function () {
    MediaFile::factory()->for(Image::factory()->create(), "mediable")->create([
        "title" => "初音ミク"
    ]);
    MediaFile::factory()->for(Image::factory()->create(), "mediable")->create([
        "title" => "重音テト"
    ]);

    $response = $this->get(route('media.index', ['mediaType' => "all", 'word' => "ミク"]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Media/Index/Index')
        ->has('medias', 1)
        ->has('medias.0', fn (Assert $page) => $page
            ->where('title', "初音ミク")
            ->etc()
        )
    );
});

test('media一覧でタグ絞り込みができる', function () {
    $tag = Tag::factory()->create();
    $mediaFiles = MediaFile::factory()->for(Image::factory()->create(), "mediable")->count(2)->create();
    $mediaFiles[0]->tags()->attach($tag);

    $response = $this->get(route('media.index', ['mediaType' => "all", 'tags' => [$tag->name]]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Media/Index/Index')
        ->has('medias', 1)
        ->has('medias.0', fn (Assert $page) => $page
            ->where('id', $mediaFiles[0]->id)
            ->etc()
        )
    );
});

test('media一覧で複数タグのアンド検索ができる', function () {
    $tags = Tag::factory()->count(2)->create();
    $mediaFiles = MediaFile::factory()->for(Image::factory()->create(), "mediable")->count(2)->create();
    $mediaFiles[0]->tags()->attach($tags);
    $mediaFiles[1]->tags()->attach($tags[0]);   // 片方のタグだけ付ける

    $response = $this->get(route('media.index', ['mediaType' => "all", 'tags' => [$tags[0]->name, $tags[1]->name]]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Media/Index/Index')
        ->has('medias', 1)
        ->has('medias.0', fn (Assert $page) => $page
            ->where('id', $mediaFiles[0]->id)
            ->etc()
        )
    );
});

test('media一覧で画面向き絞り込みができる', function () {
    $imageVertical = MediaFile::factory()->for(Image::factory()->create([
        "width" => 500,
        "height" => 1000,
        ]), "mediable")->create();
    $imageHorizon = MediaFile::factory()->for(Image::factory()->create([
        "width" => 1000,
        "height" => 500,
    ]), "mediable")->create();

    $response = $this->get(route('media.index', ['mediaType' => "all", "orientation"=>"horizon"]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Media/Index/Index')
        ->has('medias', 1)
        ->has('medias.0', fn (Assert $page) => $page
            ->where('id', $imageHorizon->id)
            ->etc()
        )
    );
});

test('media一覧で複数の検索条件を組み合わせてアンド検索ができる', function (
    string $word,
    string $tag,
    string $orientation
) {
    $tagModel = Tag::factory()->create(['name' => $tag]);
    $isHorizon = ($orientation === 'horizon');
    $width  = $isHorizon ? 1000 : 500;
    $height = $isHorizon ? 500 : 1000;

    $matchingFile = MediaFile::factory()
        ->for(Image::factory()->create([
            'width' => $width,
            'height' => $height,
        ]), 'mediable')
        ->create(['title' => $word]);
    $matchingFile->tags()->attach($tagModel);

    // 条件に合わないデータ
    MediaFile::factory()
        ->for(Image::factory()->create([
            'width' => $height, // 合うデータとは逆の比率
            'height' => $width,
        ]), 'mediable')
        ->create(['title' => '別のタイトル']);

    $response = $this->get(route('media.index', [
        'mediaType'   => "all",
        'word'        => $word,
        'tags'        => [$tag],
        'orientation' => $orientation,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) =>
    $page->component('Media/Index/Index')
        ->has('medias', 1)
        ->where('medias.0.id', $matchingFile->id)
    );
})
    ->with([
        "初音ミク",
        "重音テト"
    ])
    ->with([
        "tag1",
        "tag2",
    ])
    ->with([
        "horizon",
        "vertical"
    ]);

