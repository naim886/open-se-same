<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:blogs,slug,' . $this->route('blog')->id,
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'description'      => 'nullable|string',
            'cover_photo'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'author'           => 'nullable|string|max:100',
            'read_time'        => 'nullable|integer|min:1|max:999',
            'status'           => 'required|in:1,2',
            'faq_question'     => 'nullable|array',
            'faq_question.*'   => 'nullable|string|max:500',
            'faq_answer'       => 'nullable|array',
            'faq_answer.*'     => 'nullable|string',
        ];
    }
}
