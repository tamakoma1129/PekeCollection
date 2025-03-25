<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // ユーザーは一人なのでこの仕様に。
        $user = User::first();

        // ユーザーがいない or パスワードが不一致ならエラー
        if (
            !$user ||
            !Hash::check($this->input('password'), $user->password)
        ) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'password' => "パスワードが違います",
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // 本来はAuth::attemptで処理するが、emailが存在しないのでこの処理でログイン。
        Auth::login($user);
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'password' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * もともとはemail + ipでキーを作成していたが、emailはないのでこの処理に。
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->ip()));
    }
}
