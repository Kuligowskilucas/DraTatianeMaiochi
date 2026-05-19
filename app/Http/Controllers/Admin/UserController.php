<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // ==================== CRUD ====================

    public function index(Request $request)
    {
        $query = User::query()->with(['roles.permissions', 'permissions']);

        // Busca por nome ou email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por role (admin, secretary, patient)
        if ($role = $request->input('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        // Filtro por status (?is_active=1 ou ?is_active=0)
        if ($request->has('is_active')) {
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        $limit = min((int) $request->input('limit', 10), 100);
        $users = $query->orderBy('name')->paginate($limit);

        return response()->json([
            'data'       => UserResource::collection($users),
            'pagination' => [
                'page'       => $users->currentPage(),
                'limit'      => $users->perPage(),
                'total'      => $users->total(),
                'totalPages' => $users->lastPage(),
                'hasNext'    => $users->hasMorePages(),
                'hasPrev'    => $users->currentPage() > 1,
            ],
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $data['password'], // cast 'hashed' faz o hash
            'is_active' => $data['is_active'] ?? true,
        ]);

        $user->syncRoles($data['roles']);

        // Usuário criado por admin já entra com e-mail verificado.
        // Se quiser exigir verificação por e-mail, remova esta linha.
        $user->markEmailAsVerified();

        // Se for paciente, garante registro de Patient atrelado
        if (in_array('patient', $data['roles'], true)) {
            Patient::firstOrCreate(
                ['user_id' => $user->id],
                ['name'    => $user->name, 'email' => $user->email]
            );
        }

        $user->load(['roles.permissions', 'permissions']);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user)
    {
        $user->load(['roles.permissions', 'permissions']);
        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        $user->load(['roles.permissions', 'permissions']);
        return new UserResource($user);
    }

    public function destroy(Request $request, User $user)
    {
        abort_if(
        $request->user()->id === $user->id,
        403,
        'Você não pode deletar a própria conta.'
        );
    
        $user->tokens()->delete();

        $user->delete(); 
        return response()->json(null, 204);
    }

    // ==================== Roles e Permissões ====================

    public function assignRole(Request $request, User $user)
    {
        abort_if(
            $request->user()->id === $user->id,
            403,
            'Você não pode alterar as próprias roles.'
        );

        $data = $request->validate([
            'roles'   => ['required', 'array'],
            'roles.*' => ['string', 'in:admin,secretary,patient,doctor'],
        ]);

        $hadPatient  = $user->hasRole('patient');
        $willPatient = in_array('patient', $data['roles'], true);

        $user->syncRoles($data['roles']);

        if ($willPatient && ! $hadPatient) {
            $patient = Patient::withTrashed()
                ->firstOrCreate(
                    ['user_id' => $user->id],
                    ['name' => $user->name, 'email' => $user->email]
                );
            if ($patient->trashed()) {
                $patient->restore();
            }
        }
    
        if ($hadPatient && ! $willPatient) {
            Patient::where('user_id', $user->id)->delete();
        }

        $user->load(['roles.permissions', 'permissions']);
        return new UserResource($user);
    }

    public function givePermission(Request $request, User $user)
    {
        $data = $request->validate([
            'permissions'   => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $user->syncPermissions($data['permissions']);

        $user->load(['roles.permissions', 'permissions']);
        return new UserResource($user);
    }

    public function changePassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => $data['password']]); // cast 'hashed' hasheia

        // Por segurança, revoga tokens existentes do usuário após troca de senha
        $user->tokens()->delete();

        return response()->json(['message' => 'Senha atualizada.']);
    }

    public function changeStatus(Request $request, User $user)
    {
        abort_if(
            $request->user()->id === $user->id,
            403,
            'Você não pode alterar o status da própria conta.'
        );

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update(['is_active' => $data['is_active']]);

        // Se desativou, revoga tokens pra forçar logout
        if (! $data['is_active']) {
            $user->tokens()->delete();
        }

        $user->load(['roles.permissions', 'permissions']);
        return new UserResource($user);
    }

    /**
     * GET /api/admin/users/trash
     * Lista paginada de usuários soft-deletados.
     */
    public function trash(Request $request)
    {
        $query = User::onlyTrashed()->with(['roles.permissions', 'permissions']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $limit = min((int) $request->input('limit', 10), 100);
        $users = $query->orderBy('deleted_at', 'desc')->paginate($limit);

        return response()->json([
            'data'       => UserResource::collection($users),
            'pagination' => [
                'page'       => $users->currentPage(),
                'limit'      => $users->perPage(),
                'total'      => $users->total(),
                'totalPages' => $users->lastPage(),
                'hasNext'    => $users->hasMorePages(),
                'hasPrev'    => $users->currentPage() > 1,
            ],
        ]);
    }

    /**
     * POST /api/admin/users/{user}/restore
     * Restaura usuário soft-deletado.
     */
    public function restore(User $user)
    {
        if (! $user->trashed()) {
            return response()->json(['message' => 'Usuário não está na lixeira.'], 422);
        }
    
        $user->restore();
        $user->load(['roles.permissions', 'permissions']);
        return new UserResource($user);
    }
    
    /**
     * DELETE /api/admin/users/{user}/force
     * Exclusão permanente. Irreversível.
     */
    public function forceDestroy(Request $request, User $user)
    {
        abort_if(
            $request->user()->id === $user->id,
            403,
            'Você não pode deletar permanentemente a própria conta.'
        );
    
        $user->tokens()->delete();
        $user->forceDelete();
        return response()->json(null, 204);
    }
}