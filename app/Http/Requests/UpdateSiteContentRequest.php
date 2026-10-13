<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteContentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'label' => 'required|string|max:255',
            'value' => 'nullable|string',
            'type'  => 'required|in:text,textarea,html',
        ];
    }
}
