<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'office_id'];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
