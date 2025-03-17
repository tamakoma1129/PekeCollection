<?php

use Inertia\Testing\AssertableInertia as Assert;

test('ログイン時、パスワード編集画面へ遷移できる', function () {
    login();

    $this->get(route('password.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/Password')
        );
});

test('ゲスト時、パスワード編集画面へは遷移できない', function () {
    $this->assertGuest();

    $this->get(route('password.edit'))
        ->assertRedirect('login');
});
