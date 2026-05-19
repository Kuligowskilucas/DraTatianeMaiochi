<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = ['patient_id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['patient_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('medical_record');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function entries()
    {
        return $this->hasMany(MedicalRecordEntry::class)
            ->orderBy('created_at', 'desc');
    }
}