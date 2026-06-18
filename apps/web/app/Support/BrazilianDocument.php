<?php

namespace App\Support;

final class BrazilianDocument
{
    public static function normalize(?string $value): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper((string) $value)) ?? '';
    }

    public static function isValid(?string $value): bool
    {
        $document = self::normalize($value);

        return match (strlen($document)) {
            11 => self::isValidCpf($document),
            14 => self::isValidCnpj($document),
            default => false,
        };
    }

    public static function isValidCnpj(?string $value): bool
    {
        $cnpj = self::normalize($value);

        if (! preg_match('/^[A-Z0-9]{12}[0-9]{2}$/', $cnpj)) {
            return false;
        }

        $base = substr($cnpj, 0, 12);

        if (preg_match('/^([A-Z0-9])\1{11}$/', $base)) {
            return false;
        }

        $firstDigit = self::calculateCnpjDigit($base);
        $secondDigit = self::calculateCnpjDigit($base.$firstDigit);

        return substr($cnpj, -2) === $firstDigit.$secondDigit;
    }

    public static function cnpjFromBase(string $base): string
    {
        $base = self::normalize($base);

        if (! preg_match('/^[A-Z0-9]{12}$/', $base)) {
            throw new \InvalidArgumentException('A base do CNPJ deve possuir 12 caracteres alfanuméricos.');
        }

        $firstDigit = self::calculateCnpjDigit($base);

        return $base.$firstDigit.self::calculateCnpjDigit($base.$firstDigit);
    }

    private static function calculateCnpjDigit(string $characters): string
    {
        $weight = 2;
        $total = 0;

        for ($index = strlen($characters) - 1; $index >= 0; $index--) {
            $total += (ord($characters[$index]) - 48) * $weight;
            $weight = $weight === 9 ? 2 : $weight + 1;
        }

        $remainder = $total % 11;

        return (string) (in_array($remainder, [0, 1], true) ? 0 : 11 - $remainder);
    }

    private static function isValidCpf(string $cpf): bool
    {
        if (! preg_match('/^[0-9]{11}$/', $cpf) || preg_match('/^([0-9])\1{10}$/', $cpf)) {
            return false;
        }

        for ($digit = 9; $digit < 11; $digit++) {
            $total = 0;

            for ($index = 0; $index < $digit; $index++) {
                $total += (int) $cpf[$index] * (($digit + 1) - $index);
            }

            $calculated = (10 * $total) % 11;
            $calculated = $calculated === 10 ? 0 : $calculated;

            if ((int) $cpf[$digit] !== $calculated) {
                return false;
            }
        }

        return true;
    }
}
