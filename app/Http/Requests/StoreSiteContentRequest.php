<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteContentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'key'   => 'required|string|max:100|unique:site_contents,key|regex:/^[a-z0-9_]+$/',
            'label' => 'required|string|max:255',
            'value' => 'nullable|string',
            'type'  => 'required|in:text,textarea,html',
        ];
    }

    public function messages(): array
    {
        return [
            'key.regex' => 'Key must be lowercase letters, numbers and underscores only (e.g. return_policy).',
        ];
    }
}
