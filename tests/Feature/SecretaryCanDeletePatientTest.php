<?php

use App\Models\Patient;

it('secretary pode excluir paciente', function () {
    actingAsRole('secretary');

    $patient = Patient::factory()->create();

    $this->deleteJson("/api/patients/{$patient->id}")
        ->assertNoContent();

    // soft deleted: find returns null, withTrashed returns model
    expect(Patient::find($patient->id))->toBeNull();
    expect(Patient::withTrashed()->find($patient->id))->not->toBeNull();
});
