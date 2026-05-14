<?php

namespace App\Http\Controllers;

use App\Models\User;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = User::role('admin')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json([
            'data' => $doctors->map(fn ($u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
            ]),
        ]);
    }
}