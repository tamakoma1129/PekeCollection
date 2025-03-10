<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class AttachRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "media_ids" => ["required", "array"],
            "media_ids.*" => ["required", "integer"],
            "tags" => ["required", "array"],
            "tags.*" => ["required", "string", "max:50"],
        ];
    }


    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errorMessages = Arr::flatten($validator->errors()->getMessages());

        $response = ["message" => implode("\n", $errorMessages)];

        throw new ValidationException($validator, response()->json($response, 422));
    }
}
