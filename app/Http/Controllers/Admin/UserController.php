<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Patient;

class UserController extends Controller
{

    // ==== CRUD de Usuários (admin, secretaria, paciente) ====

    // Cria um novo usuário (admin, secretaria ou paciente)
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'email'=>['required','email','max:255','unique:users,email'],
            'password'=>['required','string','min:8','confirmed'],
            'roles'=>['required','array'], // ['admin'] ou ['secretary'] ou ['patient']
            'roles.*'=>['string','in:admin,secretary,patient'],
        ]);
        
         $user = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>$data['password'],
        ]);
        $user->syncRoles($data['roles']);

        // se for paciente com acesso ao portal, opcionalmente já cria o Patient vinculado
        if (in_array('patient',$data['roles'], true)) {
            Patient::firstOrCreate(['user_id'=>$user->id], ['name'=>$user->name,'email'=>$user->email]);
        }

        return response()->json($user, 201);
    }

    // Mostra detalhes de um usuário
    public function show(Request $request, User $user)
    {
        $request->user()->can('view', $user);
        return response()->json($user);
    }

    // Atualiza dados de um usuário
    public function update(Request $request, User $user)
    {
        $request->user()->can('update', $user);
        $data = $request->validate([
            'name'=>['sometimes','required','string','max:255'],
            'email'=>['sometimes','required','email','max:255','unique:users,email,'.$user->id],
            'password'=>['sometimes','required','string','min:8','confirmed'],
        ]);
        $user->update($data);
        return response()->json($user);
    }

    // Deleta um usuário
    public function destroy(Request $request, User $user)
    {
        $request->user()->can('delete', $user);
        $user->delete();
        return response()->json(null, 204);
    }

    // ==== Papéis e Roles de User ====

    // Sincroniza papéis (roles) de um usuário
    public function syncRoles(Request $request, User $user)
    {
        $request->user()->can('update', $user);
        $data = $request->validate([
            'roles'=>['required','array'], // ['admin'] ou ['secretary'] ou ['patient']
            'roles.*'=>['string','in:admin,secretary,patient'],
        ]);
        $user->syncRoles($data['roles']);
        return response()->json($user->roles);
    }

    // Sincroniza permissões diretas de um usuário
    public function syncPermissions(Request $request, User $user)
    {
        $request->user()->can('update', $user);
        $data = $request->validate([
            'permissions'=>['required','array'],
            'permissions.*'=>['string','exists:permissions,name'],
        ]);
        $user->syncPermissions($data['permissions']);
        return response()->json($user->permissions);
    }

    // Reset de senha (admin define nova)
    public function resetPassword(Request $request, User $user)
    {
        $request->user()->can('update', $user);
        $data = $request->validate([
            'password'=>['required','string','min:8','confirmed'],
        ]);
        $user->update(['password'=>$data['password']]);
        return response()->json(['message'=>'Senha atualizada.']);
    }

    // ==== Ativar/Desativar usuário ====   
    public function toggleStatus(Request $request, User $user)
    {
        $request->user()->can('update', $user);
        $user->is_active = !$user->is_active;
        $user->save();
        return response()->json(['is_active'=>$user->is_active]);
    }

    // ==== Tokens (Sanctum) — listar e revogar ====
    
    // Lista tokens ativos de um usuário
    public function tokensIndex(Request $request, User $user)
    {
        $request->user()->can('view', $user);
        $tokens = $user->tokens()->get(['id','name','last_used_at','created_at']);
        return response()->json($tokens);
    }

    // Revoga todos os tokens de um usuário
    public function tokensRevokeAll(Request $request, User $user)
    {
        $request->user()->can('update', $user);
        $user->tokens()->delete();
        return response()->json(['message'=>'Todos os tokens revogados.']);
    }

}
