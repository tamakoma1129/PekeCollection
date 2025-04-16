<?php

namespace App\Http\Requests\MediaFile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReuqest extends FormRequest
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
            "title" => "filled|string|max:255",
            "prev_time" => "filled|numeric",
        ];
    }
}
