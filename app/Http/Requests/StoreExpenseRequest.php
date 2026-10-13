<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'purpose'          => 'required|string|max:255',
            'date'             => 'required|date',
            'amount'           => 'required|numeric|min:0',
            'status'           => 'required|integer|in:1,2,3',
            'category'         => 'nullable|string|max:100',
            'note'             => 'nullable|string|max:2000',
            'attachment'       => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xlsx|max:5120',
            'attachment_title' => 'nullable|string|max:255',
        ];
    }
}
