<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'price'          => (float) $this->price,
            'currency'       => $this->currency->value,
            'purpose'        => $this->purpose->value,
            'type'           => $this->type->value,
            'status'         => $this->status->value,
            'area'           => (float) $this->area,
            'governorate'    => $this->whenLoaded('governorate', fn () => new GovernorateResource($this->governorate)),
            'cover_image'    => $this->whenLoaded('images', function () {
                $first = $this->images->first();
                return $first ? $first->path : null;
            }),
        ];
    }
}
