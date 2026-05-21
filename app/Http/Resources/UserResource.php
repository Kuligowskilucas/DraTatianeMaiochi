<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'emailVerifiedAt' => $this->email_verified_at?->toISOString(),
            'isActive'        => (bool) $this->is_active,
            'deletedAt'       => $this->deleted_at?->toISOString(),

            'role' => collect(['admin', 'secretary', 'doctor', 'patient'])->first(fn ($r) => $this->hasRole($r)),

            'roles'           => $this->getRoleNames()->values()->all(),
            'permissions'     => $this->getAllPermissions()->pluck('name')->values()->all(),

            'mustChangePassword' => (bool) ($this->must_change_password ?? false),

            'createdAt'       => $this->created_at?->toISOString(),
            'updatedAt'       => $this->updated_at?->toISOString(),
        ];
    }
}