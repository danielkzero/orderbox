<?php

namespace App\Rules;

use App\Support\BrazilianDocument;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBrazilianDocument implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! BrazilianDocument::isValid((string) $value)) {
            $fail('Informe um CPF ou CNPJ válido. O CNPJ pode conter letras nos 12 primeiros caracteres.');
        }
    }
}
