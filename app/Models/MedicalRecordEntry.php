<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MedicalRecordEntry extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public const TYPES = ['ANAMNESIS', 'CONSULTATION', 'FOLLOW_UP', 'NOTE'];

    protected $fillable = [
        'medical_record_id',
        'author_id',
        'appointment_id',
        'entry_type',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'confidential_notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'entry_type',
                'appointment_id',
                'subjective',
                'objective',
                'assessment',
                'plan',
                'confidential_notes',
            ])
            ->logOnlyDirty()                       
            ->dontSubmitEmptyLogs()        
            ->useLogName('medical_record_entry');
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    public function attachments()
    {
        return $this->hasMany(MedicalRecordEntryAttachment::class)
            ->orderBy('created_at');
    }
}