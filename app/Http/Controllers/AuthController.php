<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'cpf' => $request->input('cpf'),
                'password' => Hash::make($request->input('password')),
                'user_type' => $request->input('user_type'),
                'balance' => 0.00,
            ]);

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'cpf' => $user->cpf,
                    'user_type' => $user->user_type,
                    'balance' => (float) $user->balance,
                ],
                'message' => 'Usuário criado com sucesso',
            ], 201);
        } catch (\Exception $e) {
            try {
                Log::error('Registration error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } catch (\Exception $logException) {
                // Ignora erros de log
            }

            return response()->json([
                'error' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Credenciais inválidas',
                ], 401);
            }

            $user = Auth::user();

            // Gera um token simples usando hash do user ID + timestamp
            // Em produção, use Laravel Sanctum ou Passport
            $token = hash('sha256', $user->id . '-' . time() . '-' . config('app.key'));

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'balance' => (float) $user->balance,
                ],
                'message' => 'Login realizado com sucesso',
            ], 200);
        } catch (\Exception $e) {
            try {
                Log::error('Login error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } catch (\Exception $logException) {
                // Ignora erros de log
            }

            return response()->json([
                'error' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    public function me(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'error' => 'Usuário não autenticado',
                ], 401);
            }

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cpf' => $user->cpf,
                'user_type' => $user->user_type,
                'balance' => (float) $user->balance,
            ], 200);
        } catch (\Exception $e) {
            try {
                Log::error('Me endpoint error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } catch (\Exception $logException) {
                // Ignora erros de log
            }

            return response()->json([
                'error' => 'An error occurred while processing the request',
            ], 500);
        }
    }
}

