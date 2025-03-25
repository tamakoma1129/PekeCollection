<?php

test('200だけを返すhookで200が帰ってくる', function (
    string $type
) {
    login();

    $response = $this->postJson(route("tusd-hooks"),["Type" => $type]);

    $response->assertOk();
})
    ->with([
        "post-create",
        "post-receive",
        "post-terminate"
    ]);

test('200だけを返すhookで非ログイン時は200が帰ってこない', function (
    string $type
) {
    $this->assertGuest();
    $response = $this->postJson(route("tusd-hooks"),["Type" => $type]);

    $response->assertUnauthorized();
})
    ->with([
        "post-create",
        "post-receive",
        "post-terminate"
    ]);


test('tusdhooksのリクエストエラーテスト', function (
    string $type,
    string $message
) {
    login();

    $response = $this->postJson(route("tusd-hooks"),["Type" => $type]);

    expect($response->json())
        ->toMatchArray([
            "errors" => [
                "Type" => [
                    0 => $message,
                ]
            ]
        ]);
})
    ->with([
        ["", "typeは必須です。"],
        ["fake-type", "選択されたtypeは無効です。"]
    ]);
