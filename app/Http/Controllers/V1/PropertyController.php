<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyFilterRequest;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\V1\GovernorateCountResource;
use App\Http\Resources\V1\PropertyCardResource;
use App\Http\Resources\V1\PropertyDetailResource;
use App\Models\Property;
use App\Services\PropertyCacheService;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class PropertyController extends Controller
{
    public function __construct(
        private PropertyService $service,
        private PropertyCacheService $cache,
    ) {}

    public function index(PropertyFilterRequest $request): AnonymousResourceCollection
    {
        $properties = $this->service->listCursor(
            $request,
            with: ['governorate', 'images']
        );

        return PropertyCardResource::collection($properties);
    }

    public function show(Property $property): PropertyDetailResource
    {
        $user = request()->user() ?? request()->user('sanctum');

        if (! Gate::forUser($user)->allows('view', $property)) {
            abort(404);
        }

        $property = $this->cache->getPropertyDetail($property->id, function () use ($property) {
            $property->loadMissing(['governorate', 'office.logo', 'owner', 'images']);
            return $property;
        });

        return new PropertyDetailResource($property);
    }

    public function store(StorePropertyRequest $request): JsonResponse
    {
        $property = $this->service->store($request);

        $property->loadMissing(['governorate', 'office.logo', 'owner', 'images']);

        return (new PropertyDetailResource($property))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdatePropertyRequest $request, Property $property): PropertyDetailResource
    {
        $property = $this->service->update($request, $property);

        $property->loadMissing(['governorate', 'office.logo', 'owner', 'images']);

        return new PropertyDetailResource($property);
    }

    public function destroy(Property $property): JsonResponse
    {
        $this->authorize('delete', $property);

        $this->service->delete($property);

        return response()->json(['message' => 'تم حذف العقار بنجاح.']);
    }

    public function featured(): AnonymousResourceCollection
    {
        $properties = $this->cache->getFeatured();

        return PropertyCardResource::collection($properties);
    }

    public function countsByGovernorate(): AnonymousResourceCollection
    {
        $governorates = $this->cache->getGovernoratePropertyCounts();

        return GovernorateCountResource::collection($governorates);
    }
}
