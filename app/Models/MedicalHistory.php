<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalHistory extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['patient_id','author_id','appointment_id','type','content'];

    public function patient(){ return $this->belongsTo(Patient::class); }
    public function author(){ return $this->belongsTo(User::class,'author_id'); }
    public function appointment(){ return $this->belongsTo(Appointment::class); }
}
