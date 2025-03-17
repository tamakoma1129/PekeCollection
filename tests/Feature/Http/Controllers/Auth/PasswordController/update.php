<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('ログイン時、パスワードを変更できる', function () {
    $user = login();
    $newPassword = 'newPassword';
    $this->assertTrue(Hash::check("password", $user->password));

    $response = $this
        ->from(route('password.edit'))
        ->put(route('password.update',[
            "current_password" => "password",
            "password" => $newPassword,
            "password_confirmation" => $newPassword,
        ]));

    $response
        ->assertRedirect(route('password.edit'))
        ->assertSessionHasNoErrors();
    $this->assertTrue(Hash::check($user->fresh()->password, $newPassword));
});

test('ゲスト時、パスワードを変更できない', function () {
    $user = User::factory()->create();
    $this->assertGuest();

    $newPassword = 'newPassword';
    $this->assertTrue(Hash::check("password", $user->password));

    $response = $this
        ->from(route('password.edit'))
        ->put(route('password.update',[
            "current_password" => "password",
            "password" => $newPassword,
            "password_confirmation" => $newPassword,
        ]));

    $response
        ->assertRedirect(route('login'));
    $this->assertTrue(! Hash::check($user->fresh()->password, $newPassword));
    $this->assertGuest();
});

test('パスワードが違う場合、パスワードを変更できない', function () {
    $user = login();
    $newPassword = 'newPassword';
    $this->assertTrue(Hash::check("password", $user->password));

    $response = $this
        ->from(route('password.edit'))
        ->put(route('password.update',[
            "current_password" => "wrongPassword",
            "password" => $newPassword,
            "password_confirmation" => $newPassword,
        ]));

    $response
        ->assertRedirect(route('password.edit'))
        ->assertInvalid(["current_password" => "パスワードが正しくありません"]);
    $this->assertTrue(! Hash::check($user->fresh()->password, $newPassword));
});

test('パスワード変更のバリデーションパスチェック', function (
    string $new_password,
) {
    $user = login();
    $this->assertTrue(Hash::check("password", $user->password));

    $this
        ->put(route('password.update',[
            "current_password" => "password",
            "password" => $new_password,
            "password_confirmation" => $new_password,
        ]))
        ->assertSessionHasNoErrors();
})
    ->with([
        "password",
        fn() => Str::random(8),
        fn() => Str::random(1000),
    ]);

test('パスワード変更のバリデーションエラーチェック', function (
    string $inputField,
    string $inputValue,
    string $expectedError,
) {
    $user = login();
    $this->assertTrue(Hash::check("password", $user->password));

    $this
        ->put(route('password.update',[
            $inputField => $inputValue,
        ]))
        ->assertInvalid([$inputField => $expectedError]);
})
    ->with([
        ["current_password", "", "現在のパスワードは必須です。"],
        ["current_password", "wrongPassword", "パスワードが正しくありません"],
        ["password", "", "パスワードは必須です。"],
        ["password", fn() => Str::random(7), "パスワードは少なくとも8文字である必要があります。"],
        ["password", "password", "パスワードの確認が一致しません。"]
    ]);
