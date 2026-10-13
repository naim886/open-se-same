<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product')->id;

        return [
            'product_type'        => 'required|in:simple,group_with_variant',
            'category_id'         => 'required|exists:categories,id',
            'name'                => 'required|string|max:255',
            'slug'                => 'required|string|max:255|unique:products,slug,' . $productId,
            'short_description'   => 'required|string|max:500',
            'description'         => 'nullable|string',
            'cost'                => 'nullable|numeric|min:0',
            'price'               => 'required_if:product_type,simple|nullable|numeric|min:0',
            'sale_price'          => 'nullable|numeric|min:0|lt:price',
            'stock'               => 'required_if:product_type,simple|nullable|integer|min:0',
            'meta_title'          => 'nullable|string|max:255',
            'meta_description'    => 'nullable|string|max:500',
            'status'              => 'required|in:0,1',
            'sort_order'          => 'required|integer|min:1',
            'primary_photo'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg,avif|max:2048',
            'gallery.*'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg,avif|max:2048',
            'og_image'            => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg,avif|max:2048',
            'specifications'      => 'nullable|array',
            'specifications.*.specification_id' => 'required_with:specifications|exists:specifications,id',
            'specifications.*.value' => 'required_with:specifications|string|max:255',
            'variants'            => 'required_if:product_type,group_with_variant|array',
            'variants.*.price'    => 'required_with:variants|numeric|min:0',
            'variants.*.stock'    => 'required_with:variants|integer|min:0',
            'variants.*.cost'     => 'nullable|numeric|min:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
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
            'product_type.required'      => 'The product type field is required.',
            'category_id.required'       => 'The category field is required.',
            'category_id.exists'         => 'The selected category is invalid.',
            'name.required'              => 'The product name field is required.',
            'slug.required'              => 'The product slug field is required.',
            'slug.unique'                => 'The product slug has already been taken.',
            'short_description.required' => 'The short description field is required.',
            'price.required_if'          => 'The price field is required for simple products.',
            'stock.required_if'          => 'The stock field is required for simple products.',
            'sale_price.lt'              => 'The sale price must be less than the regular price.',
            'status.required'            => 'The status field is required.',
            'sort_order.required'        => 'The sort order field is required.',
            'primary_photo.image'        => 'The primary photo must be an image.',
            'variants.required_if'       => 'Variants are required for variant products.',
            'variants.*.price.required_with' => 'Price is required for all variants.',
            'variants.*.stock.required_with' => 'Stock is required for all variants.',
        ];
    }
}
