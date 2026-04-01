<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class N8nSecretMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedSecret = (string) config('services.n8n.secret');
        $providedSecret = (string) $request->header('X-N8N-Secret');

        if ($expectedSecret === '') {
            return new JsonResponse([
                'message' => 'N8N secret is not configured.',
            ], 500);
        }

        if (! hash_equals($expectedSecret, $providedSecret)) {
            return new JsonResponse([
                'message' => 'Unauthorized n8n request.',
            ], 401);
        }

        return $next($request);
    }
}
