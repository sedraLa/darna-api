<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'description'    => $this->description,
            'price'          => (float) $this->price,
            'currency'       => $this->currency->value,
            'purpose'        => $this->purpose->value,
            'type'           => $this->type->value,
            'status'         => $this->status->value,
            'area'           => (float) $this->area,
            'governorate'    => $this->whenLoaded('governorate', fn () => new GovernorateResource($this->governorate)),
            'office'         => $this->whenLoaded('office', fn () => new OfficeResource($this->office)),
            'owner'          => $this->whenLoaded('owner', function () use ($request) {
                $owner = $this->owner;
                return [
                    'id'    => $owner->id,
                    'name'  => $owner->name,
                    // Phone is only visible to authenticated users
                    'phone' => $request->user() ? $owner->phone : null,
                ];
            }),
            'images'         => $this->whenLoaded('images', fn () => ImageResource::collection($this->images)),
            'created_at'     => $this->created_at->toDateTimeString(),
            'updated_at'     => $this->updated_at->toDateTimeString(),
        ];
    }
}
