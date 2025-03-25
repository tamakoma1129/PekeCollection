<?php



test('ログアウトできる', function () {
    login();
    $this->assertAuthenticated();

    $this
        ->post(route("logout"))
        ->assertRedirect("/");

    $this->assertGuest();
});
