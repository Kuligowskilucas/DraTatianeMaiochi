<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Appointment;
use App\Models\MedicalHistory;

class Patient extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id','name','email','phone','birth_date','document','notes'];

    public function user(){ return $this->belongsTo(User::class); }
    public function appointments(){ return $this->hasMany(Appointment::class); }
    public function histories(){ return $this->hasMany(MedicalHistory::class); }
}

