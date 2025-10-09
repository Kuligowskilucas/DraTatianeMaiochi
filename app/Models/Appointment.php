<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['patient_id','created_by','doctor_id','starts_at','duration_minutes','status','location','notes'];
    protected $casts = ['starts_at'=>'datetime'];

    public function patient(){ return $this->belongsTo(Patient::class); }
    public function creator(){ return $this->belongsTo(User::class,'created_by'); }
    public function doctor(){ return $this->belongsTo(User::class,'doctor_id'); }
}

