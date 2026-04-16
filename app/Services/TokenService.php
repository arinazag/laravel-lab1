<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class TokenService
{
    private string $secret;
    private int $accessTtl;
    private int $refreshTtl;
    private int $maxActiveTokens;

    public function __construct()
    {
        $this->secret = env('TOKEN_SECRET', 'MySuperSecretKey123!@#');
        $this->accessTtl = (int) env('ACCESS_TOKEN_TTL', 60);
        $this->refreshTtl = (int) env('REFRESH_TOKEN_TTL', 10080);
        $this->maxActiveTokens = (int) env('MAX_ACTIVE_TOKENS', 5);
    }

    /**
     * Генерация пары токенов
     */
    public function generateTokens(User $user): array
    {
        $tokenId = $this->generateTokenId();
        
        // Проверяем лимит активных токенов
        $this->checkAndEvictTokens($user);
        
        $accessToken = $this->createToken($user, $tokenId, 'access', $this->accessTtl);
        $refreshToken = $this->createToken($user, $tokenId, 'refresh', $this->refreshTtl);
        
        // Сохраняем метаданные токена
        $this->storeTokenMetadata($user->id, $tokenId);
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * Проверка валидности токена
     */
    public function validateToken(string $token): ?object
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        
        list($headerEncoded, $payloadEncoded, $signature) = $parts;
        
        // Проверяем подпись
        $expectedSignature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secret);
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }
        
        $payload = json_decode(base64_decode($payloadEncoded));
        
        // Проверяем срок действия
        if ($payload->exp < time()) {
            return null;
        }
        
        // Проверяем, не отозван ли токен
        $cacheKey = "token:{$payload->user_id}:{$payload->token_id}";
        if (!Cache::has($cacheKey)) {
            return null;
        }
        
        return $payload;
    }

    /**
     * Отзыв конкретного токена
     */
    public function revokeToken(int $userId, string $tokenId): void
    {
        $cacheKey = "token:{$userId}:{$tokenId}";
        Cache::forget($cacheKey);
        
        // Удаляем из списка токенов пользователя
        $userTokens = Cache::get("user_tokens:{$userId}", []);
        $userTokens = array_filter($userTokens, fn($id) => $id !== $tokenId);
        Cache::put("user_tokens:{$userId}", $userTokens);
    }

    /**
     * Отзыв всех токенов пользователя
     */
    public function revokeAllTokens(User $user): void
    {
        $userTokens = Cache::get("user_tokens:{$user->id}", []);
        foreach ($userTokens as $tokenId) {
            Cache::forget("token:{$user->id}:{$tokenId}");
        }
        Cache::forget("user_tokens:{$user->id}");
    }

    /**
     * Обновление токенов
     */
    public function refreshTokens(string $refreshToken): ?array
    {
        $payload = $this->validateToken($refreshToken);
        
        if (!$payload || $payload->type !== 'refresh') {
            return null;
        }
        
        $user = User::find($payload->user_id);
        if (!$user) {
            return null;
        }
        
        // Отзываем старый токен
        $this->revokeToken($payload->user_id, $payload->token_id);
        
        // Генерируем новую пару
        return $this->generateTokens($user);
    }

    /**
     * Получение списка активных токенов пользователя
     */
    public function getUserTokens(User $user): array
    {
        $userTokens = Cache::get("user_tokens:{$user->id}", []);
        $tokens = [];
        
        foreach ($userTokens as $tokenId) {
            $cacheKey = "token:{$user->id}:{$tokenId}";
            $metadata = Cache::get($cacheKey);
            if ($metadata) {
                $tokens[] = [
                    'token_id' => $tokenId,
                    'created_at' => $metadata['created_at'],
                ];
            }
        }
        
        return $tokens;
    }

    private function generateTokenId(): string
    {
        return bin2hex(random_bytes(16));
    }

    private function createToken(User $user, string $tokenId, string $type, int $ttl): string
    {
        $payload = [
            'user_id' => $user->id,
            'username' => $user->username,
            'token_id' => $tokenId,
            'type' => $type,
            'iat' => time(),
            'exp' => time() + ($ttl * 60),
        ];
        
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        
        $headerEncoded = base64_encode(json_encode($header));
        $payloadEncoded = base64_encode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secret);
        
        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    private function checkAndEvictTokens(User $user): void
    {
        $userTokens = Cache::get("user_tokens:{$user->id}", []);
        
        if (count($userTokens) >= $this->maxActiveTokens) {
            // Удаляем самый старый токен
            $oldestTokenId = array_shift($userTokens);
            Cache::forget("token:{$user->id}:{$oldestTokenId}");
            Cache::put("user_tokens:{$user->id}", $userTokens);
        }
    }

    private function storeTokenMetadata(int $userId, string $tokenId): void
    {
        $metadata = [
            'created_at' => now()->toDateTimeString(),
        ];
        
        Cache::put("token:{$userId}:{$tokenId}", $metadata, now()->addMinutes($this->refreshTtl));
        
        $userTokens = Cache::get("user_tokens:{$userId}", []);
        $userTokens[] = $tokenId;
        Cache::put("user_tokens:{$userId}", $userTokens);
    }
}