<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'queue_id',
        'blood_pressure',
        'height',
        'weight',
        'temperature',
        'complaint',
        'diagnosa',
        'saran',
        'pemeriksaan',
        'teraphy',
        'note',
        'tindakan',
        'vital_sign_status',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
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
