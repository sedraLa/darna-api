<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'phone'   => $this->phone,
            'address' => $this->address,
            'logo'    => $this->whenLoaded('logo', fn () => $this->logo?->path),
        ];
    }
}
