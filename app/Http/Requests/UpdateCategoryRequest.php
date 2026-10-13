<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => ['required', 'string', 'max:255', 'unique:categories,slug,' . $this->category->id],
            'short_description' => ['required', 'string', 'max:255'],
            'status'            => ['required', 'string', 'in:' . implode(',', array_keys(\App\Models\Category::STATUS_LIST))],
            'sort_order'        => ['required', 'numeric'],
            'icon'              => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'banner'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name'              => 'Category Name',
            'slug'              => 'Category Slug',
            'short_description' => 'Short Description',
            'status'            => 'Status',
            'sort_order'        => 'Sort Order',
            'icon'              => 'Category Icon',
            'banner'            => 'Category Banner',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required'              => 'The category name is required.',
            'slug.required'              => 'The category slug is required.',
            'slug.unique'                => 'This slug is already taken. Please choose a different one.',
            'short_description.required' => 'The short description is required.',
            'status.required'            => 'Please select a status.',
            'status.in'                  => 'Please select a valid status.',
            'sort_order.required'        => 'The sort order is required.',
            'sort_order.numeric'         => 'The sort order must be a number.',
            'icon.image'                 => 'The icon must be an image file.',
            'icon.mimes'                 => 'The icon must be a file of type: jpeg, png, jpg, webp.',
            'icon.max'                   => 'The icon may not be greater than 2MB.',
            'banner.image'               => 'The banner must be an image file.',
            'banner.mimes'               => 'The banner must be a file of type: jpeg, png, jpg, webp.',
            'banner.max'                 => 'The banner may not be greater than 2MB.',
        ];
    }
}
