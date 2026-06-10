<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\DTO\UserDTO;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();
        $data = $users->map(fn($user) => UserDTO::fromModel($user))->values();
        return response()->json($data);
    }
}