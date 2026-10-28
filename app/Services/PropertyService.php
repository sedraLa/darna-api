<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Enums\UserRole;
use App\Http\Requests\PropertyFilterRequest;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use App\Models\Scopes\AvailableScope;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PropertyService
{
    public function list(PropertyFilterRequest $request): LengthAwarePaginator
    {
        $query = Property::query();

        if ($request->filled('governorate_id')) {
            $query->forGovernorate((int) $request->governorate_id);
        }

        if ($request->filled('purpose')) {
            $query->forPurpose($request->enum('purpose', \App\Enums\PropertyPurpose::class));
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

        // status filter is only allowed when the global scope is already bypassed
        // (e.g., office viewing its own listings). For public queries, only
        // available properties are visible due to AvailableScope.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function listForOffice(PropertyFilterRequest $request, int $officeId): LengthAwarePaginator
    {
        $query = Property::forOffice($officeId);

        if ($request->filled('governorate_id')) {
            $query->forGovernorate((int) $request->governorate_id);
        }

        if ($request->filled('purpose')) {
            $query->forPurpose($request->enum('purpose', \App\Enums\PropertyPurpose::class));
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(StorePropertyRequest $request): Property
    {
        $user     = $request->user();
        $officeId = $user->offices()->value('id');

        $data = $request->validated();

        $data['office_id'] = $officeId;
        $data['status']    = $data['status'] ?? PropertyStatus::Pending->value;

        return Property::withoutGlobalScope(AvailableScope::class)
            ->create($data);
    }

    public function update(UpdatePropertyRequest $request, Property $property): Property
    {
        $property->update($request->validated());

        return $property->fresh();
    }

    public function delete(Property $property): void
    {
        $property->withoutGlobalScope(AvailableScope::class);
        $property->delete();
    }
}
