<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MedicalRecordEntryAttachment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'medical_record_entry_id',
        'file_path',
        'original_name',
        'mime',
        'size',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['original_name', 'mime', 'size'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('medical_record_entry_attachment');
    }

    public function entry()
    {
        return $this->belongsTo(MedicalRecordEntry::class, 'medical_record_entry_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}