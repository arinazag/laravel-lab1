<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TokenService;

class AuthTokenMiddleware
{
    protected TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }
        
        $payload = $this->tokenService->validateToken($token);
        
        if (!$payload) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }
        
        // Проверяем, что это access token
        if ($payload->type !== 'access') {
            return response()->json(['message' => 'Invalid token type'], 401);
        }
        
        // Добавляем информацию о пользователе в запрос
        $request->merge([
            'auth_user_id' => $payload->user_id,
            'auth_token_id' => $payload->token_id,
        ]);
        
        return $next($request);
    }
}