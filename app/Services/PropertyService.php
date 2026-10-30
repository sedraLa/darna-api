<?php

namespace App\Services;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Http\Requests\PropertyFilterRequest;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use App\Models\Scopes\AvailableScope;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PropertyService
{
    public function __construct(private PropertyCacheService $cache) {}

    public function list(PropertyFilterRequest $request, array $with = []): LengthAwarePaginator
    {
        $query = Property::query()->with($with);

        $this->applyFilters($query, $request);

        return $query->paginate($request->integer('per_page', 15));
    }

    public function listCursor(PropertyFilterRequest $request, array $with = []): CursorPaginator
    {
        $query = Property::query()
            ->with($with)
            ->orderBy('id');

        $this->applyFilters($query, $request);

        return $query->cursorPaginate($request->integer('per_page', 15));
    }

    public function listForOffice(PropertyFilterRequest $request, int $officeId, array $with = []): LengthAwarePaginator
    {
        $query = Property::forOffice($officeId)->with($with);

        $this->applyFilters($query, $request, allowStatus: true);

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(StorePropertyRequest $request): Property
    {
        $officeId = $request->user()->offices()->value('id');

        $data              = $request->validated();
        $data['office_id'] = $officeId;
        $data['status']    = $data['status'] ?? PropertyStatus::Pending->value;

        $property = Property::withoutGlobalScope(AvailableScope::class)->create($data);

        $this->cache->invalidateAfterCreate($property);

        return $property;
    }

    public function update(UpdatePropertyRequest $request, Property $property): Property
    {
        $property->update($request->validated());
        $property->refresh();

        $this->cache->invalidateAfterUpdate($property);

        return $property;
    }

    public function delete(Property $property): void
    {
        $this->cache->invalidateAfterDelete($property);

        $property->delete();
    }

    private function applyFilters(\Illuminate\Database\Eloquent\Builder $query, PropertyFilterRequest $request, bool $allowStatus = false): void
    {
        if ($request->filled('governorate_id')) {
            $query->forGovernorate((int) $request->governorate_id);
        }

        if ($request->filled('purpose')) {
            $query->forPurpose($request->enum('purpose', PropertyPurpose::class));
        }

        if ($request->filled('price_min') || $request->filled('price_max')) {
            $query->priceRange(
                $request->filled('price_min') ? (float) $request->price_min : null,
                $request->filled('price_max') ? (float) $request->price_max : null,
            );
        }

        if ($request->filled('area_min') || $request->filled('area_max')) {
            $query->areaRange(
                $request->filled('area_min') ? (float) $request->area_min : null,
                $request->filled('area_max') ? (float) $request->area_max : null,
            );
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('office_id')) {
            $query->where('office_id', (int) $request->office_id);
        }

        if ($allowStatus && $request->filled('status')) {
            $query->where('status', $request->status);
        }
    }
}
