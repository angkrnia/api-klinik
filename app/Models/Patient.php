<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'record_no',
        'fullname',
        'gender',
        'birthday',
        'age',
        'phone',
        'address',
        'no_ktp',
        'nama_keluarga'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function history()
    {
        return $this->hasMany(History::class);
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (isset($patient->no_ktp)) {
                $patient->no_ktp = Crypt::encrypt($patient->no_ktp);
            }
        });

        static::updating(function ($patient) {
            if (isset($patient->no_ktp)) {
                $patient->no_ktp = Crypt::encrypt($patient->no_ktp);
            }
        });
    }

    public function getNoKtpAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (!$value) {
            return null;
        }

        return Crypt::decrypt($value);
    }
}
