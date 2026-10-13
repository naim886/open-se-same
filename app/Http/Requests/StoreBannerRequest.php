<?php

namespace App\Http\Requests;

use App\Models\Banner;
use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
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
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'button_text' => 'nullable|string|max:50',
            'button_url'  => 'nullable|string|max:255',
            'image'       => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order'  => 'required|integer|min:1',
            'status'      => 'required|integer|in:' . Banner::STATUS_ACTIVE . ',' . Banner::STATUS_INACTIVE,
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title'       => 'Banner Title',
            'description' => 'Description',
            'button_text' => 'Button Text',
            'button_url'  => 'Button URL',
            'image'       => 'Banner Image',
            'sort_order'  => 'Sort Order',
            'status'      => 'Status',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'      => 'The banner title is required.',
            'title.max'           => 'The banner title may not be greater than 255 characters.',
            'image.required'      => 'Please upload a banner image.',
            'image.image'         => 'The file must be an image.',
            'image.mimes'         => 'The image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max'           => 'The image may not be greater than 2MB.',
            'sort_order.required' => 'The sort order is required.',
            'sort_order.integer'  => 'The sort order must be a number.',
            'sort_order.min'      => 'The sort order must be at least 1.',
            'status.required'     => 'Please select a status.',
            'status.in'           => 'Please select a valid status.',
        ];
    }
}
