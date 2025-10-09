<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\MedicalHistory;

class MedicalHistoryController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    
    public function store(Request $request, Patient $patient)
    {
        $this->authorize('createHistory', [MedicalHistory::class, $patient]);
        $data = $request->validate([
            'appointment_id' => ['nullable','exists:appointments,id'],
            'type' => ['nullable','string','max:50'],
            'content' => ['required','string'],
        ]);
        $history = MedicalHistory::create([
            'patient_id' => $patient->id,
            'author_id'  => $request->user()->id,
            'appointment_id' => $data['appointment_id'] ?? null,
            'type' => $data['type'] ?? 'NOTE',
            'content' => $data['content'],
        ]);
        return response()->json($history, 201);
    }
}
