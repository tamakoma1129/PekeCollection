<?php

it('returns a successful response', function () {
    $this->assertEquals('testing', config('app.env'));
    $response = $this->get('/');

    $response->assertStatus(302);
});
