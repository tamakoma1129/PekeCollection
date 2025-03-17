<?php

use App\Models\User;

test('ユーザーを作成できる', function () {
    $this->assertGuest();
    $this->assertDatabaseCount('users', 0);
    $password = Str::random(random_int(8, 1000));

    $response = $this->post(route('register', [
        "password" => $password,
        "password_confirmation" => $password
    ]));

    $response
        ->assertRedirect(route("dashboard"));
    $this->assertAuthenticated();
    $this->assertDatabaseCount('users', 1);
});

test('ユーザーが既に作成されていたらユーザー作成できない', function () {
    User::factory()->create();
    $this->assertGuest();
    $this->assertDatabaseCount('users', 1);
    $password = "password";

    $response = $this->post(route('register', [
        "password" => $password,
        "password_confirmation" => $password
    ]));

    $response
        ->assertInvalid(["password" => "既にユーザーが作成されています。"]);
    $this->assertGuest();
    $this->assertDatabaseCount('users', 1);
});

test('ユーザー作成のバリデーションパスチェック', function (
    string $password,
) {
    $this->assertGuest();
    $this->assertDatabaseCount('users', 0);

    $this->post(route('register', [
        "password" => $password,
        "password_confirmation" => $password
    ]))
        ->assertRedirect(route("dashboard"))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('users', 1);
    $this->assertAuthenticated();
})
    ->with([
        fn() => Str::random(8),
        fn() => Str::random(1000),
    ]);

test('ユーザー作成のバリデーションエラーチェック', function (
    string $inputField,
    string $inputValue,
    string $expectedError,
) {
    $this->assertGuest();
    $this->assertDatabaseCount('users', 0);

    $this->post(route('register', [
        $inputField => $inputValue,
    ]))
        ->assertInvalid([$inputField => $expectedError]);

    $this->assertDatabaseCount('users', 0);
    $this->assertGuest();
})
    ->with([
        ["password", "", "パスワードは必須です。"],
        ["password", fn() => Str::random(7), "パスワードは少なくとも8文字である必要があります。"],
        ["password", "password", "パスワードの確認が一致しません。"]
    ]);
