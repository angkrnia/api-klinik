<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    const SELESAI = 'done';
    const BATAL = 'canceled';

    protected $table = 'queue_logs';
    protected $fillable = [
        'queue',
        'patient_id',
        'doctor_id',
        'status',
        'is_last_queue',
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

    public function scopeKeywordSearch(Builder $query, string $searchKeyword): Builder
    {
        $columns = $this->fillable;
        return $query->where(function ($query) use ($searchKeyword, $columns) {
            foreach ($columns as $column) {
                $query->orWhere($column, 'LIKE', "%$searchKeyword%");
            }
        });
    }
}
