<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RadPostAuth extends Model
{
    protected $table = 'radpostauth';
    public $timestamps = false;

    protected $casts = [
        'authdate' => 'datetime',
    ];

    public function isSuccess(): bool
    {
        return str_contains(strtolower($this->reply), 'accept');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) {
            return $query;
        }
        return $query->where('username', 'like', "%{$search}%");
    }

    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'success' => $query->where('reply', 'like', '%Accept%'),
            'failed'  => $query->where('reply', 'not like', '%Accept%'),
            default   => $query,
        };
    }

    public function scopeByDateFrom(Builder $query, ?string $date): Builder
    {
        if (!$date) {
            return $query;
        }
        return $query->whereDate('authdate', '>=', $date);
    }

    public function scopeByDateTo(Builder $query, ?string $date): Builder
    {
        if (!$date) {
            return $query;
        }
        return $query->whereDate('authdate', '<=', $date);
    }
}
