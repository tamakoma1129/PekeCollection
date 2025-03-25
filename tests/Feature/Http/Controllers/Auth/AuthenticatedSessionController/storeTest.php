<?php

use App\Models\User;

test('パスワードが合っていればログインできる', function () {
    $user = User::factory()->create();
    $this->assertGuest();

    $response = $this->post(route('login', ["password" => "password"]));
    $response
        ->assertRedirect(route("media.index", ["mediaType"=>"all"]));

    $this->assertAuthenticatedAs($user);
});

test('パスワードが合っていないとログインできない', function () {
    User::factory()->create();
    $this->assertGuest();

    $this
        ->from(route("login"))
        ->post(route('login', ["password" => "wrongPassword"]))
        ->assertRedirect(route("login"))
        ->assertInvalid(["password" => "パスワードが違います"]);

    $this->assertGuest();
});

test("パスワードに5回間違えるとロックがかかる", function () {
    User::factory()->create();
    $this->assertGuest();

    $this->freezeSecond(function () {
        for ($i = 0; $i < 5; $i++) {
            $this
                ->post(route("login", [
                    "password" => "wrongPassword"
                ]));
        }

        // 6回目
        $this
            ->from(route("login"))
            ->post(route("login", [
                "password" => "wrongPassword"
            ]))
            ->assertRedirect(route("login"))
            ->assertSessionHasErrors([
                "password" => "ログイン試行回数が多すぎます。60秒後に再試行してください。",
            ]);
    });

    $this->assertGuest();
});

