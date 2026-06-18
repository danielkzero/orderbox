<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function states(): JsonResponse
    {
        return response()->json(Cache::remember('locations:ibge:states', now()->addDay(), function (): array {
            return Http::acceptJson()
                ->timeout(8)
                ->retry(2, 200)
                ->get('https://servicodados.ibge.gov.br/api/v1/localidades/estados', ['orderBy' => 'nome'])
                ->throw()
                ->json();
        }));
    }

    public function municipalities(string $state): JsonResponse
    {
        abort_unless(preg_match('/^[A-Z]{2}$/', strtoupper($state)), 422, 'UF inválida.');
        $state = strtoupper($state);

        return response()->json(Cache::remember("locations:ibge:municipalities:{$state}", now()->addDay(), function () use ($state): array {
            return Http::acceptJson()
                ->timeout(10)
                ->retry(2, 200)
                ->get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$state}/municipios", ['orderBy' => 'nome'])
                ->throw()
                ->json();
        }));
    }

    public function zipCode(string $zipCode): JsonResponse
    {
        $zipCode = preg_replace('/\D/', '', $zipCode);
        abort_unless(strlen($zipCode) === 8, 422, 'CEP inválido.');

        $data = Cache::remember("locations:viacep:{$zipCode}", now()->addDays(7), function () use ($zipCode): array {
            return Http::acceptJson()
                ->timeout(8)
                ->retry(2, 200)
                ->get("https://viacep.com.br/ws/{$zipCode}/json/")
                ->throw()
                ->json();
        });

        abort_if($data['erro'] ?? false, 404, 'CEP não encontrado.');

        return response()->json($data);
    }
}
