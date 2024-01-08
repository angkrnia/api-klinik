<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'queue_id',
        'complaint',
        'diagnosa',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }
}
