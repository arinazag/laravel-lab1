<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\DTO\UserDTO;
use App\DTO\AuthSuccessDTO;
use App\DTO\TokenListDTO;
use App\Services\TokenService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function register(RegisterRequest $request)
    {
        $dto = $request->toDTO();

        $user = User::create([
    'username' => $dto->username,
    'name' => $dto->username,
    'email' => $dto->email,
    'password' => Hash::make($request->password),
    'birthday' => $dto->birthday,
]);

        $userDTO = new UserDTO(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday,
        );

        return response()->json($userDTO->toArray(), 201);
    }

    public function login(LoginRequest $request)
    {
        $dto = $request->toDTO();

        $user = User::where('username', $dto->username)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $tokens = $this->tokenService->generateTokens($user);

        $userDTO = new UserDTO(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday,
        );

        $authSuccessDTO = new AuthSuccessDTO(
            access_token: $tokens['access_token'],
            refresh_token: $tokens['refresh_token'],
            user: $userDTO,
        );

        return response()->json($authSuccessDTO->toArray(), 200);
    }

    public function me(Request $request)
    {
        $user = User::find($request->auth_user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userDTO = new UserDTO(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday,
        );

        return response()->json($userDTO->toArray(), 200);
    }

    public function out(Request $request)
    {
        $this->tokenService->revokeToken($request->auth_user_id, $request->auth_token_id);

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function tokens(Request $request)
    {
        $user = User::find($request->auth_user_id);
        $tokens = $this->tokenService->getUserTokens($user);

        $tokenListDTO = new TokenListDTO($tokens);

        return response()->json($tokenListDTO->toArray(), 200);
    }

    public function outAll(Request $request)
    {
        $user = User::find($request->auth_user_id);
        $this->tokenService->revokeAllTokens($user);

        return response()->json(['message' => 'Logged out from all devices'], 200);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return response()->json(['message' => 'Refresh token required'], 400);
        }

        $result = $this->tokenService->refreshTokens($refreshToken);

        if (!$result) {
            return response()->json(['message' => 'Invalid or expired refresh token. All tokens revoked for security.'], 401);
        }

        return response()->json($result, 200);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = User::find($request->auth_user_id);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        $this->tokenService->revokeAllTokens($user);

        return response()->json(['message' => 'Password changed successfully'], 200);
    }
}
