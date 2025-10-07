<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VerifyEmailController extends Controller
{
    public function verify(Request $request, $id, $hash): JsonResponse
    {
        $user = $request->user();

        // Segurança extra: o ID do path deve ser do usuário autenticado
        if (! $user || (string) $user->getKey() !== (string) $id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        // Valida o hash recebido (igual ao padrão do Laravel)
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Link de verificação inválido.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'E-mail já verificado.'], 200);
        }

        // Marca como verificado + dispara evento
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'E-mail verificado com sucesso.'], 200);
    }

    public function send(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'E-mail já verificado.'], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'E-mail de verificação reenviado.'], 200);
    }
}
