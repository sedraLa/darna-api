<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Scopes\AvailableScope;
use App\Traits\HasImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    use HasImages;

    protected $fillable = [
        'title',
        'description',
        'price',
        'currency',
        'purpose',
        'status',
        'type',
        'area',
        'governorate_id',
        'office_id',
        'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'currency' => Currency::class,
            'purpose'  => PropertyPurpose::class,
            'status'   => PropertyStatus::class,
            'type'     => PropertyType::class,
            'price'    => 'decimal:2',
            'area'     => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new AvailableScope());
    }

    // Returns all properties for a specific office, bypassing the public AvailableScope.
    // This keeps the scope bypass scoped to one office so no other office's data leaks.
    public static function forOffice(int $officeId): Builder
    {
        return static::withoutGlobalScope(AvailableScope::class)
            ->where('office_id', $officeId);
    }

    public function scopeForGovernorate(Builder $query, int $governorateId): Builder
    {
        return $query->where('governorate_id', $governorateId);
    }

    public function scopeForPurpose(Builder $query, PropertyPurpose $purpose): Builder
    {
        return $query->where('purpose', $purpose->value);
    }

    public function scopePriceRange(Builder $query, ?float $min, ?float $max): Builder
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('price', '<=', $max);
        }

        return $query;
    }

    public function scopeAreaRange(Builder $query, ?float $min, ?float $max): Builder
    {
        if ($min !== null) {
            $query->where('area', '>=', $min);
        }

        if ($max !== null) {
            $query->where('area', '<=', $max);
        }

        return $query;
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }
}
