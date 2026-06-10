<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\DTO\UserDTO;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }
}