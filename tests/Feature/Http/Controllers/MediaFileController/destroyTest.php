<?php

use App\Models\Audio;
use App\Models\Image;
use App\Models\Manga;
use App\Models\MangaPage;
use App\Models\MediaFile;
use App\Models\Tag;
use App\Models\Video;

beforeEach(function () {
    login();
    Storage::fake('private');

    // image,audio,video,mangaを作成する
    $this->image = MediaFile::factory()->for(Image::factory(), "mediable")->create([
        "path" => "uploads/images/image.png",
        "mediable_type" => Image::class,
    ]);
    Storage::disk("private")->makeDirectory("extras/images/image.png/");
    Storage::disk("private")->put("uploads/images/image.png", "dummy");

    $this->audio = MediaFile::factory()->for(Audio::factory(), "mediable")->create([
        "path" => "uploads/audios/audio.mp3",
        "mediable_type" => Audio::class,
    ]);
    Storage::disk("private")->makeDirectory("extras/audios/audio.mp3/");
    Storage::disk("private")->put("uploads/audios/audio.mp3", "dummy");

    $this->video = MediaFile::factory()->for(Video::factory(), "mediable")->create([
        "path" => "uploads/videos/video.mp4",
        "mediable_type" => Video::class,
    ]);
    Storage::disk("private")->makeDirectory("extras/videos/video.mp4/");
    Storage::disk("private")->put("uploads/videos/video.mp4", "dummy");

    $this->manga = MediaFile::factory()->for(Manga::factory()->create(), "mediable")->create([
        "path" => "uploads/mangas/manga/",
        "mediable_type" => Manga::class,
    ]);
    $this->mangaPage = MangaPage::factory()->for($this->manga->mediable, "manga")->create();
    Storage::disk("private")->makeDirectory("extras/mangas/manga/");
    Storage::disk("private")->makeDirectory("uploads/mangas/manga/");
});

test('画像ファイルを削除できる', function () {
    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->delete(route('media_file.destroy'), [
            "media_ids" => [$this->image->id],
            "password" => "password",
        ]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertSessionHasNoErrors();

    // ファイルのアサート
    Storage::disk("private")->assertMissing("uploads/images/image.png");
    Storage::disk("private")->assertExists("uploads/audios/audio.mp3");
    Storage::disk("private")->assertExists("uploads/videos/video.mp4");
    Storage::disk("private")->assertExists("uploads/mangas/manga/");

    Storage::disk("private")->assertMissing("extras/images/image.png/");
    Storage::disk("private")->assertExists("extras/audios/audio.mp3/");
    Storage::disk("private")->assertExists("extras/videos/video.mp4/");
    Storage::disk("private")->assertExists("extras/mangas/manga/");

    // データのアサート
    $this->assertDatabaseCount("images", 0);
    $this->assertDatabaseCount("audios", 1);
    $this->assertDatabaseCount("videos", 1);
    $this->assertDatabaseCount("mangas", 1);
    $this->assertDatabaseCount("manga_pages", 1);
    $this->assertDatabaseMissing("media_files",["id" => $this->image->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->audio->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->video->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->manga->id]);
});

test('音源ファイルを削除できる', function () {
    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->delete(route('media_file.destroy'), [
            "media_ids" => [$this->audio->id],
            "password" => "password",
        ]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertSessionHasNoErrors();

    // ファイルのアサート
    Storage::disk("private")->assertExists("uploads/images/image.png");
    Storage::disk("private")->assertMissing("uploads/audios/audio.mp3");
    Storage::disk("private")->assertExists("uploads/videos/video.mp4");
    Storage::disk("private")->assertExists("uploads/mangas/manga/");

    Storage::disk("private")->assertExists("extras/images/image.png/");
    Storage::disk("private")->assertMissing("extras/audios/audio.mp3/");
    Storage::disk("private")->assertExists("extras/videos/video.mp4/");
    Storage::disk("private")->assertExists("extras/mangas/manga/");

    // データのアサート
    $this->assertDatabaseCount("images", 1);
    $this->assertDatabaseCount("audios", 0);
    $this->assertDatabaseCount("videos", 1);
    $this->assertDatabaseCount("mangas", 1);
    $this->assertDatabaseCount("manga_pages", 1);
    $this->assertDatabaseHas("media_files",["id" => $this->image->id]);
    $this->assertDatabaseMissing("media_files", ["id" => $this->audio->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->video->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->manga->id]);
});

test('動画ファイルを削除できる', function () {
    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->delete(route('media_file.destroy'), [
            "media_ids" => [$this->video->id],
            "password" => "password",
        ]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertSessionHasNoErrors();

    // ファイルのアサート
    Storage::disk("private")->assertExists("uploads/images/image.png");
    Storage::disk("private")->assertExists("uploads/audios/audio.mp3");
    Storage::disk("private")->assertMissing("uploads/videos/video.mp4");
    Storage::disk("private")->assertExists("uploads/mangas/manga/");

    Storage::disk("private")->assertExists("extras/images/image.png/");
    Storage::disk("private")->assertExists("extras/audios/audio.mp3/");
    Storage::disk("private")->assertMissing("extras/videos/video.mp4/");
    Storage::disk("private")->assertExists("extras/mangas/manga/");

    // データのアサート
    $this->assertDatabaseCount("images", 1);
    $this->assertDatabaseCount("audios", 1);
    $this->assertDatabaseCount("videos", 0);
    $this->assertDatabaseCount("mangas", 1);
    $this->assertDatabaseCount("manga_pages", 1);
    $this->assertDatabaseHas("media_files",["id" => $this->image->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->audio->id]);
    $this->assertDatabaseMissing("media_files", ["id" => $this->video->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->manga->id]);
});

test('漫画ファイルを削除できる', function () {
    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->delete(route('media_file.destroy'), [
            "media_ids" => [$this->manga->id],
            "password" => "password",
        ]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertSessionHasNoErrors();

    // ファイルのアサート
    Storage::disk("private")->assertExists("uploads/images/image.png");
    Storage::disk("private")->assertExists("uploads/audios/audio.mp3");
    Storage::disk("private")->assertExists("uploads/videos/video.mp4");
    Storage::disk("private")->assertMissing("uploads/mangas/manga/");

    Storage::disk("private")->assertExists("extras/images/image.png/");
    Storage::disk("private")->assertExists("extras/audios/audio.mp3/");
    Storage::disk("private")->assertExists("extras/videos/video.mp4/");
    Storage::disk("private")->assertMissing("extras/mangas/manga/");

    // データのアサート
    $this->assertDatabaseCount("images", 1);
    $this->assertDatabaseCount("audios", 1);
    $this->assertDatabaseCount("videos", 1);
    $this->assertDatabaseCount("mangas", 0);
    $this->assertDatabaseCount("manga_pages", 0);
    $this->assertDatabaseHas("media_files",["id" => $this->image->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->audio->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->video->id]);
    $this->assertDatabaseMissing("media_files", ["id" => $this->manga->id]);
});

test('複数削除できる', function () {
    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->delete(route('media_file.destroy'), [
            "media_ids" => [$this->manga->id, $this->image->id],
            "password" => "password",
        ]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertSessionHasNoErrors();

    // ファイルのアサート
    Storage::disk("private")->assertMissing("uploads/images/image.png");
    Storage::disk("private")->assertExists("uploads/audios/audio.mp3");
    Storage::disk("private")->assertExists("uploads/videos/video.mp4");
    Storage::disk("private")->assertMissing("uploads/mangas/manga/");

    Storage::disk("private")->assertMissing("extras/images/image.png/");
    Storage::disk("private")->assertExists("extras/audios/audio.mp3/");
    Storage::disk("private")->assertExists("extras/videos/video.mp4/");
    Storage::disk("private")->assertMissing("extras/mangas/manga/");

    // データのアサート
    $this->assertDatabaseCount("images", 0);
    $this->assertDatabaseCount("audios", 1);
    $this->assertDatabaseCount("videos", 1);
    $this->assertDatabaseCount("mangas", 0);
    $this->assertDatabaseCount("manga_pages", 0);
    $this->assertDatabaseMissing("media_files",["id" => $this->image->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->audio->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->video->id]);
    $this->assertDatabaseMissing("media_files", ["id" => $this->manga->id]);
});

test('ゲストは削除できない', function () {
    auth()->logout();

    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->delete(route('media_file.destroy'), [
            "media_ids" => [$this->image->id],
            "password" => "password",
        ]);

    $response->assertRedirect(route('login'));

    // ファイルのアサート
    Storage::disk("private")->assertExists("uploads/images/image.png");
    Storage::disk("private")->assertExists("uploads/audios/audio.mp3");
    Storage::disk("private")->assertExists("uploads/videos/video.mp4");
    Storage::disk("private")->assertExists("uploads/mangas/manga/");

    Storage::disk("private")->assertExists("extras/images/image.png/");
    Storage::disk("private")->assertExists("extras/audios/audio.mp3/");
    Storage::disk("private")->assertExists("extras/videos/video.mp4/");
    Storage::disk("private")->assertExists("extras/mangas/manga/");

    // データのアサート
    $this->assertDatabaseCount("images", 1);
    $this->assertDatabaseCount("audios", 1);
    $this->assertDatabaseCount("videos", 1);
    $this->assertDatabaseCount("mangas", 1);
    $this->assertDatabaseCount("manga_pages", 1);
    $this->assertDatabaseHas("media_files",["id" => $this->image->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->audio->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->video->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->manga->id]);
});

test('パスワード違ったら消せない', function () {
    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->delete(route('media_file.destroy'), [
            "media_ids" => [$this->image->id],
            "password" => "wrongPassword",
        ]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertInvalid(["password" => "パスワードが正しくありません。"]);

    // ファイルのアサート
    Storage::disk("private")->assertExists("uploads/images/image.png");
    Storage::disk("private")->assertExists("uploads/audios/audio.mp3");
    Storage::disk("private")->assertExists("uploads/videos/video.mp4");
    Storage::disk("private")->assertExists("uploads/mangas/manga/");

    Storage::disk("private")->assertExists("extras/images/image.png/");
    Storage::disk("private")->assertExists("extras/audios/audio.mp3/");
    Storage::disk("private")->assertExists("extras/videos/video.mp4/");
    Storage::disk("private")->assertExists("extras/mangas/manga/");

    // データのアサート
    $this->assertDatabaseCount("images", 1);
    $this->assertDatabaseCount("audios", 1);
    $this->assertDatabaseCount("videos", 1);
    $this->assertDatabaseCount("mangas", 1);
    $this->assertDatabaseCount("manga_pages", 1);
    $this->assertDatabaseHas("media_files",["id" => $this->image->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->audio->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->video->id]);
    $this->assertDatabaseHas("media_files", ["id" => $this->manga->id]);
});

test('メディアを削除した後、使われているタグが0個のものがあったらそのタグを削除される', function () {
    $tag = Tag::factory()->create();
    $this->image->tags()->attach($tag);

    $this->assertDatabaseCount("tags", 1);
    $this->assertDatabaseCount("media_file_tag", 1);

    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->delete(route('media_file.destroy'), [
            "media_ids" => [$this->image->id],
            "password" => "password",
        ]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount("tags", 0);
    $this->assertDatabaseCount("media_file_tag", 0);
});

test('メディアを削除した後でも、タグが他についていたらそのタグは消えない', function () {
    $tag = Tag::factory()->create();
    $this->image->tags()->attach($tag);
    $this->manga->tags()->attach($tag); // 漫画は消さない

    $this->assertDatabaseCount("tags", 1);
    $this->assertDatabaseCount("media_file_tag", 2);

    $response = $this
        ->from(route('media.index', ['mediaType' => "all"]))
        ->delete(route('media_file.destroy'), [
            "media_ids" => [$this->image->id],
            "password" => "password",
        ]);

    $response->assertRedirect(route('media.index', ['mediaType' => "all"]))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount("tags", 1);
    $this->assertDatabaseCount("media_file_tag", 1);
});
