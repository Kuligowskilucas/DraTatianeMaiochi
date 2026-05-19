<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\ChangeOwnPasswordRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $deviceName = $data['device_name'] ?? ($request->ip() . ' | API');
        if (isset($data['device_name'])) {
            $user->tokens()->where('name', $deviceName)->delete();
        }

        $token = $user->createToken($deviceName, ['*'])->plainTextToken;

        return response()->json([
            'user'       => new UserResource($user),
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => 60 * 60 * 24,
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user->fresh());
    }

    public function changePassword(ChangeOwnPasswordRequest $request)
    {
        $user = $request->user();
        $currentTokenId = $request->user()->currentAccessToken()->id;

        $user->update(['password' => $request->validated()['password']]);

        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return response()->json(['message' => 'Senha atualizada com sucesso.']);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout efetuado.']);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Todos os tokens revogados.']);
    }
}

