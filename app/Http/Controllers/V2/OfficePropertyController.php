<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyFilterRequest;
use App\Http\Resources\V2\PropertyCardResource;
use App\Services\PropertyService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfficePropertyController extends Controller
{
    public function __construct(private PropertyService $service) {}

    public function index(PropertyFilterRequest $request): AnonymousResourceCollection
    {
        $user   = $request->user();
        $office = $user->offices()->firstOrFail();

        $properties = $this->service->listForOffice(
            $request,
            $office->id,
            with: ['governorate', 'images']
        );

        return PropertyCardResource::collection($properties);
    }
}
