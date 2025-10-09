<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Patient;

class UserController extends Controller
{
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
}
