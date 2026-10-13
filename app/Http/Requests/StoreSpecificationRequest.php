<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'specifications_name' => 'required|string|max:255|unique:specifications,specifications_name',
            'status'             => 'required|in:1,2',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'specifications_name.required' => 'The specification name field is required.',
            'specifications_name.unique'   => 'The specification name has already been taken.',
            'status.required'              => 'The status field is required.',
            'status.in'                    => 'The selected status is invalid.',
        ];
    }
}
