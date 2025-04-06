<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel("login", function (User $user) {
    return $user !== null;
});
