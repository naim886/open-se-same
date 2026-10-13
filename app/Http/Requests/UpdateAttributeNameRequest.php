<?php

namespace App\Http\Requests;

use App\Models\AttributeName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttributeNameRequest extends FormRequest
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
        $attributeNameId = $this->route('attribute_name')->id ?? $this->route('attributeName')->id;

        return [
            'name'   => ['required', 'string', 'max:255', 'unique:attribute_names,name,' . $attributeNameId],
            'status' => ['required', 'integer', 'in:' . implode(',', array_keys(AttributeName::STATUS_LIST))],
        ];
    }
}
