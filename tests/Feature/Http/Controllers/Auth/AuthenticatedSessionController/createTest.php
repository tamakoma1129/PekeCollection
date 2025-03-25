<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('ゲストならログイン画面へ行ける', function () {
    User::factory()->create();
    $this->assertGuest();

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Login')
        );

    $this->assertGuest();
});

test('ログイン済みならログイン画面へ行けない', function () {
    $user = login();

    $this
        ->get(route('login'))
        ->assertRedirect(route("dashboard"));

    $this->assertAuthenticatedAs($user);
});
