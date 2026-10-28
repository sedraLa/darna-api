<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyFilterRequest;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\V2\PropertyCardResource;
use App\Http\Resources\V2\PropertyDetailResource;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class PropertyController extends Controller
{
    public function __construct(private PropertyService $service) {}

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
        $user = request()->user();

        if (! Gate::forUser($user)->allows('view', $property)) {
            abort(404);
        }

        $property->loadMissing(['governorate', 'office.logo', 'owner', 'images']);

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
}
