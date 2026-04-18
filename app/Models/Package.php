<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = ['groupname', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function attributes(): HasMany
    {
        return $this->hasMany(PackageAttribute::class)->orderBy('sort_order');
    }
}
