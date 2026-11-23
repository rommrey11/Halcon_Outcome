<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = trim($validated['login']);

        $user = User::with('role')
            ->where('email', $login)
            ->orWhere('name', $login)
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials. Please check your username/email and password.'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your employee account is deactivated. Contact an Administrator.'
            ], 403);
        }

        return response()->json([
            'message' => 'Login successful',
            'user'    => $user,
        ], 200);
    }
}