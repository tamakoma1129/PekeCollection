<?php

namespace App\Http\Requests\MediaFile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class TusdHookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Auth::check();
    }

    public function rules(): array
    {
        return [
            "Type" => [
                "required",
                "string",
                Rule::in([
                    "pre-create",
                    "post-create",
                    "post-receive",
                    "post-finish",
                    "post-terminate"
                ]),
            ]
        ];
    }
}
