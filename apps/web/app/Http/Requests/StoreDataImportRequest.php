<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDataImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['Admin', 'Manager'], true);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['initial', 'regions', 'products', 'customers', 'payment_methods', 'payment_terms'])],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ];
    }
}
