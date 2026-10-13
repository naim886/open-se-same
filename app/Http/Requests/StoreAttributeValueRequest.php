<?php

namespace App\Http\Requests;

use App\Models\AttributeValue;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttributeValueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    final public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    final public function rules(): array
    {
        return [
            'attribute_name_id' => ['required', 'integer', 'exists:attribute_names,id'],
            'name'              => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_values')->where(function ($query) {
                    return $query->where('attribute_name_id', $this->input('attribute_name_id'));
                })
            ],
            'status'            => ['required', 'integer', 'in:' . implode(',', array_keys(AttributeValue::STATUS_LIST))],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    final public function messages(): array
    {
        return [
            'name.unique' => 'This attribute value already exists for the selected attribute name.',
        ];
    }
}
