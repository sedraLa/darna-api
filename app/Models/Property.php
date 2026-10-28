<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Traits\HasImages;
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
