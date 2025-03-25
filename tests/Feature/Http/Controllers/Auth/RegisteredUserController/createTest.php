<?php

use Inertia\Testing\AssertableInertia as Assert;

test('ゲストなら登録画面へ行ける', function () {
    $this->assertGuest();

    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Register')
        );

    $this->assertGuest();
});

test('ログイン済みなら登録画面へ行けない', function () {
    $user = login();

    $this
        ->get(route('register'))
        ->assertRedirect(route("dashboard"));

    $this->assertAuthenticatedAs($user);
});
