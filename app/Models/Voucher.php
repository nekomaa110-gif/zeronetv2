<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'prefix', 'password', 'type', 'package_id', 'session_seconds', 'calendar_hours',
        'status', 'first_login_at', 'expired_at', 'note', 'created_by',
    ];

    protected $casts = [
        'first_login_at' => 'datetime',
        'expired_at'     => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) return $query;
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
              ->orWhere('note', 'like', "%{$search}%");
        });
    }

    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if (!$status) return $query;
        return $query->where('status', $status);
    }

    public function scopeByType(Builder $query, ?string $type): Builder
    {
        if (!$type) return $query;
        return $query->where('type', $type);
    }
}
