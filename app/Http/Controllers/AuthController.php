<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    // Registro de novo usuário
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->sendEmailVerificationNotification();

        // Cria um token imediatamente (ou você pode exigir verificação de e-mail antes)
        $token = $user->createToken($request->ip() . ' | API')->plainTextToken;

        return response()->json([
            'message' => 'Usuário registrado com sucesso. Verifique seu e-mail para confirmar.',
            'user'  => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], Response::HTTP_CREATED);
    }

    // Login e criação de token
    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        // Opcional: revogar tokens antigos do mesmo device_name
        $deviceName = $data['device_name'] ?? ($request->ip() . ' | API');
        if (isset($data['device_name'])) {
            $user->tokens()->where('name', $deviceName)->delete();
        }

        $token = $user->createToken($deviceName, ['*'])->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // Retorna dados do usuário autenticado
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    // Logout do token atual
    public function logout(Request $request)
    {
        // Revoga apenas o token usado nesta requisição
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout efetuado.']);
    }

    // Logout de todas as sessões (revoga todos os tokens do usuário)
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Todos os tokens revogados.']);
    }
}

