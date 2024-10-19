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

    public function setNoKtpAttribute($value)
    {
        $this->attributes['no_ktp'] = encrypt($value);
    }

    public function getNoKtpAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (!$value) {
            return null;
        }

        return decrypt($value);
    }

    public function getPhoneAttribute($value)
    {
        if (preg_match('/^628/',  $value)) {
            return '0' . substr($value, 2);
        } else {
            return $value;
        }
    }
}
