<?php

namespace App\Http\Requests;

use App\Models\HomepageSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHomepageSettingsRequest extends FormRequest
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
            'sections'               => ['nullable', 'array'],
            'sections.*.id'          => ['nullable', 'integer', 'exists:homepage_sections,id'],
            'sections.*.type'        => ['required', Rule::in(array_keys(HomepageSection::TYPE_LIST))],
            'sections.*.category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                'required_if:sections.*.type,' . HomepageSection::TYPE_CATEGORY,
            ],
            'sections.*.title'       => ['nullable', 'string', 'max:255'],
            'sections.*.status'      => ['required', 'in:0,1'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'sections.*.type'        => 'block type',
            'sections.*.category_id' => 'block category',
            'sections.*.title'       => 'block title',
            'sections.*.status'      => 'block status',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'sections.*.type.required'        => 'Every block needs a type.',
            'sections.*.category_id.required_if' => 'Please choose a category for each category block.',
            'sections.*.category_id.exists'   => 'One of the selected categories is invalid.',
        ];
    }
}
