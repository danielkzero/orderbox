<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiClientIsAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-OrderBox-Client-Key');
        $secret = $request->header('X-OrderBox-Client-Secret');

        $client = $key
            ? ApiClient::query()->where('client_key', $key)->where('active', true)->first()
            : null;

        if (! $client || ! $secret || ! Hash::check($secret, $client->secret_hash)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'api_client_not_allowed',
                    'message' => 'API client is not allowed.',
                ],
            ], 403);
        }

        $client->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('api_client', $client);

        return $next($request);
    }
}
