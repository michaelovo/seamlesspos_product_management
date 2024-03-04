<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
            'name' => 'required|string|max:150|min:2|unique:products',
            'description' => 'required|string|min:3|max:500',
            'price' => 'numeric|required',
            'quantity' => 'integer|required|min:1|digits_between: 1,9',
            'category' => 'required|array',
            'category.*' => 'required|integer|distinct|exists:categories,id',
        ];
    }
}
