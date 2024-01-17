<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    const SELESAI = 'selesai';

    protected $table = 'queue_logs';
    protected $fillable = [
        'queue',
        'patient_id',
        'doctor_id',
        'status',
    ];

    public function history()
    {
        return $this->hasOne(History::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
