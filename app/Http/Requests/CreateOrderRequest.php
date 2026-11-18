<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.product_id' => ['required', 'string', Rule::exists('products', 'slug')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'discount' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
