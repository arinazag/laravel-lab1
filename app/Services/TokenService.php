<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

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
     * Generate a pair of tokens (access + refresh)
     */
    public function generateTokens(User $user): array
    {
        $tokenId = $this->generateTokenId();

        $this->checkAndEvictTokens($user);

        $accessToken = $this->createToken($user, $tokenId, 'access', $this->accessTtl);
        $refreshToken = $this->createToken($user, $tokenId, 'refresh', $this->refreshTtl);

        $this->storeTokenMetadata($user->id, $tokenId);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    /**
     * Validate a token and return its payload, or null if invalid
     */
    public function validateToken(string $token): ?object
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($headerEncoded, $payloadEncoded, $signature) = $parts;

        $expectedSignature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $this->secret);
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $payload = json_decode(base64_decode($payloadEncoded));

        if ($payload->exp < time()) {
            return null;
        }

        $cacheKey = "token:{$payload->user_id}:{$payload->token_id}";
        if (!Cache::has($cacheKey)) {
            return null;
        }

        return $payload;
    }

    /**
     * Revoke a specific token
     */
    public function revokeToken(int $userId, string $tokenId): void
    {
        $cacheKey = "token:{$userId}:{$tokenId}";
        Cache::forget($cacheKey);

        $userTokens = Cache::get("user_tokens:{$userId}", []);
        $userTokens = array_filter($userTokens, fn($id) => $id !== $tokenId);
        Cache::put("user_tokens:{$userId}", $userTokens);
    }

    /**
     * Revoke all tokens for a user
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
     * Refresh tokens using a refresh token.
     * If token is invalid/used, revokes ALL tokens for security.
     */
    public function refreshTokens(string $refreshToken): ?array
    {
        // Try to extract user_id even from expired token for security revocation
        $payload = $this->decodeTokenPayload($refreshToken);

        // Validate token fully
        $validPayload = $this->validateToken($refreshToken);

        if (!$validPayload || $validPayload->type !== 'refresh') {
            // Security measure: revoke ALL tokens if refresh token is invalid
            if ($payload && isset($payload->user_id)) {
                $user = User::find($payload->user_id);
                if ($user) {
                    $this->revokeAllTokens($user);
                }
            }
            return null;
        }

        $user = User::find($validPayload->user_id);
        if (!$user) {
            return null;
        }

        // Revoke the old token pair
        $this->revokeToken($validPayload->user_id, $validPayload->token_id);

        // Generate new pair
        return $this->generateTokens($user);
    }

    /**
     * Get list of active tokens for a user
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
                    'expires_at' => $metadata['expires_at'],
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

    /**
     * Decode token payload WITHOUT validation (used for security revocation)
     */
    private function decodeTokenPayload(string $token): ?object
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payloadEncoded = $parts[1];
        $payload = json_decode(base64_decode($payloadEncoded));

        return $payload ?: null;
    }

    private function checkAndEvictTokens(User $user): void
    {
        $userTokens = Cache::get("user_tokens:{$user->id}", []);

        if (count($userTokens) >= $this->maxActiveTokens) {
            // Remove the oldest token
            $oldestTokenId = array_shift($userTokens);
            Cache::forget("token:{$user->id}:{$oldestTokenId}");
            Cache::put("user_tokens:{$user->id}", $userTokens);
        }
    }

    private function storeTokenMetadata(int $userId, string $tokenId): void
    {
        $metadata = [
            'created_at' => now()->toDateTimeString(),
            'expires_at' => now()->addMinutes($this->refreshTtl)->toDateTimeString(),
        ];

        Cache::put("token:{$userId}:{$tokenId}", $metadata, now()->addMinutes($this->refreshTtl));

        $userTokens = Cache::get("user_tokens:{$userId}", []);
        $userTokens[] = $tokenId;
        Cache::put("user_tokens:{$userId}", $userTokens);
    }
}