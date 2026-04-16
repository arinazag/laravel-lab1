<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\DTO\UserDTO;
use App\DTO\AuthSuccessDTO;
use App\DTO\TokenListDTO;
use App\Services\TokenService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Регистрация нового пользователя
     * POST /api/auth/register
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'birthday' => $request->birthday,
        ]);

        $userDTO = new UserDTO(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            birthday: $user->birthday,
        );

        return response()->json($userDTO->toArray(), 201);
    }

    /**
     * Авторизация (логин)
     * POST /api/auth/login
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
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

    /**
     * Получение информации о текущем пользователе
     * GET /api/auth/me
     */
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

    /**
     * Выход (отзыв текущего токена)
     * POST /api/auth/out
     */
    public function out(Request $request)
    {
        $this->tokenService->revokeToken($request->auth_user_id, $request->auth_token_id);

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    /**
     * Получение списка активных токенов
     * GET /api/auth/tokens
     */
    public function tokens(Request $request)
    {
        $user = User::find($request->auth_user_id);
        $tokens = $this->tokenService->getUserTokens($user);

        $tokenListDTO = new TokenListDTO($tokens);

        return response()->json($tokenListDTO->toArray(), 200);
    }

    /**
     * Выход со всех устройств
     * POST /api/auth/out_all
     */
    public function outAll(Request $request)
    {
        $user = User::find($request->auth_user_id);
        $this->tokenService->revokeAllTokens($user);

        return response()->json(['message' => 'Logged out from all devices'], 200);
    }

    /**
     * Обновление токена доступа
     * POST /api/auth/refresh
     */
    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return response()->json(['message' => 'Refresh token required'], 400);
        }

        $tokens = $this->tokenService->refreshTokens($refreshToken);

        if (!$tokens) {
            return response()->json(['message' => 'Invalid or expired refresh token'], 401);
        }

        return response()->json($tokens, 200);
    }

    /**
     * Изменение пароля
     * POST /api/auth/change-password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|regex:/^(?=.*[0-9])(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).*$/',
            'c_password' => 'required|same:new_password',
        ]);

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