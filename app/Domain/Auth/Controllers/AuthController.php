<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\User\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController
{
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                Log::warning('Failed login attempt', [
                    'email' => $request->email,
                ]);

                return response()->json([
                    'error' => 'Credenciais inválidas.',
                ], 401);
            }

            $user->tokens()->delete();

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed login', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao realizar login.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logout realizado com sucesso.']);
        } catch (\Throwable $e) {
            Log::error('Failed logout', [
                'user_uid' => $request->user()->uid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Erro ao realizar logout.',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }
}
