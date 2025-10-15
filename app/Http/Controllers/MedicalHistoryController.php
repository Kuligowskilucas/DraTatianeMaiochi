<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\MedicalHistory;

class MedicalHistoryController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    // Mostra detalhes de um histórico médico
    public function show(MedicalHistory $history) {
            $this->authorize('view', $history);
            return response()->json($history);
        }
    
    // Cria um novo registro no histórico médico do paciente
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

    // Lista históricos médicos de um paciente
    public function myHistories(Patient $patient)
    {
        $this->authorize('viewAny', [MedicalHistory::class, $patient]);
        $items = MedicalHistory::where('patient_id',$patient->id)
            ->orderBy('created_at','desc')->paginate(20);
        return response()->json($items);
    }

}
