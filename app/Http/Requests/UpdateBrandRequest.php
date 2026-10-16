<?php

namespace App\Http\Requests;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
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
            'slug'              => ['required', 'string', 'max:255', 'unique:brands,slug,' . $this->brand->id],
            'short_description' => ['required', 'string', 'max:500'],
            'status'            => ['required', 'in:' . implode(',', array_keys(Brand::STATUS_LIST))],
            'sort_order'        => ['required', 'integer', 'min:1'],
            'logo'              => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'banner'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name'              => 'Brand Name',
            'slug'              => 'Brand Slug',
            'short_description' => 'Short Description',
            'status'            => 'Status',
            'sort_order'        => 'Sort Order',
            'logo'              => 'Brand Logo',
            'banner'            => 'Brand Banner',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'slug.unique'                => 'This slug is already taken. Please choose a different one.',
            'short_description.required' => 'The short description is required.',
            'status.in'                  => 'Please select a valid status.',
            'logo.image'                 => 'The logo must be an image file.',
            'banner.image'               => 'The banner must be an image file.',
        ];
    }
}
